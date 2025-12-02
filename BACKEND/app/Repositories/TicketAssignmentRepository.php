<?php

namespace App\Repositories;

use App\Models\SupportTicket;
use App\Models\AssignmentHistory;
use App\Jobs\AssignTicketJob;
use App\Http\Response\CustomApiResponse;
use App\Models\AssignmentRule;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketAssignmentRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function manualAssign($ticketId, $request)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);
            $agentId = $request->input('agent_id');

            $ticket->assign_to_id = $agentId;
            $ticket->save();

            AssignmentHistory::create([
                'ticket_id' => $ticket->id,
                'assigned_to_id' => $agentId,
                'assigned_by_id' => Auth::id(),
                'assignment_method' => 'manual',
                'reason' => $request->input('reason', 'Manual assignment'),
            ]);

            return $this->apiResponse->success($ticket, 'Ticket manually assigned.');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function autoAssign($ticketId)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);

            generateLogs('info', 'before dispatch');
            AssignTicketJob::dispatch($ticket);
            generateLogs('info', 'after dispatch');
            // return $this->apiResponse->success($ticket, 'Ticket auto-assignment queued.');
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticket,
                'Ticket auto-assignment queued.'
            );
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function unassign($ticketId)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);

            $ticket->assign_to_id = null;
            $ticket->save();

            AssignmentHistory::create([
                'ticket_id' => $ticket->id,
                'assigned_to_id' => null,
                'assigned_by_id' => Auth::id(),
                'assignment_method' => 'manual',
                'reason' => 'Unassigned',
            ]);

            return $this->apiResponse->success($ticket, 'Ticket unassigned.');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function autoAssignApi($ticketId)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);

            generateLogs("info", "inside the assign ticket job");
            // Skip if already assigned
            if ($ticket->assign_to_id && $ticket->assign_to_id > 1) {   // user_id = 1 is defined for Admin
                return;
            }

            // If created by agent → assign to same agent
            // if ($ticket->created_by && $ticket->creator->role_id === 3) {
            //     $this->assign($ticket->creator->id, 'self_assign');
            //     return;
            // }

            // Auto assignment via rules
            $rule = $this->findApplicableRule($ticket);
            generateLogs("info", "rule found " . json_encode($rule));
            if (!$rule) {
                // Fallback: unassigned
                $this->assign(0, 'unassigned', $ticket, null, 'No matching rule found');
                return;
            }

            $agentId = null;

            switch ($rule->method) {
                case 'round_robin':
                    generateLogs("info", "CASE Round Robin");
                    $agentId = $this->roundRobinAssign($rule);
                    break;

                case 'least_loaded':
                    generateLogs("info", "CASE Least Loaded");
                    $agentId = $this->leastLoadedAssign($rule);
                    break;

                case 'random':
                    generateLogs("info", "CASE Random");
                    $agentId = $this->randomAssign($rule);
                    break;

                case 'live_agent':
                    generateLogs("info", "CASE Live Agent Assignment");
                    $agentId = $this->liveAgentAssign($rule);
                    break;

                default:
                    generateLogs("info", "CASE Default (round robin)");
                    $agentId = $this->roundRobinAssign($rule); // fallback to round_robin
                    break;
            }

            if ($agentId) {
                $this->assign($agentId, $rule->method, $ticket, $rule->id);
            } else {
                $this->assign(0, 'unassigned', $ticket, $rule->id, 'No eligible agent found');
            }
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticket,
                'Ticket auto-assignment queued.'
            );
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    protected function findApplicableRule(SupportTicket $ticket): ?AssignmentRule
    {
        return AssignmentRule::query()
            ->where(function ($q) use ($ticket) {
                if ($ticket->incident_id) {
                    $q->where('incident_type_id', $ticket->incident_id);
                }
            })
            // ->orWhere(function ($q) use ($ticket) {
            ->where(function ($q) use ($ticket) {
                if ($ticket->department_id) {
                    $q->where('department_id', $ticket->department_id);
                }
            })
            ->where('enabled', true)
            ->orderByDesc('priority_order')
            ->first();
    }

    protected function roundRobinAssign(AssignmentRule $rule): ?int
    {
        return DB::transaction(function () use ($rule) {
            generateLogs("info", "inside round robin funciton");
            $params = $rule->parameters ?? [];
            generateLogs("info", "found params" . json_encode($params));
            $eligible = $this->eligibleAgents($rule);
            generateLogs("info", "found agent " . json_encode($eligible));

            if (empty($eligible)) {
                generateLogs("info", "eligible empty");
                return null;
            }

            $lastId = $params['round_robin']['last_agent_id'] ?? null;
            $sorted = collect($eligible)->sort()->values();

            $nextIdx = 0;
            if ($lastId && ($idx = $sorted->search($lastId)) !== false) {
                $nextIdx = ($idx + 1) % $sorted->count();
            }

            $nextAgent = $sorted[$nextIdx];

            // update cursor in JSON
            $params['round_robin']['last_agent_id'] = $nextAgent;
            $rule->parameters = $params;
            $rule->save();
            generateLogs("info", "returnig next agent " . json_encode($nextAgent));
            return $nextAgent;
        });
    }

    protected function leastLoadedAssign(AssignmentRule $rule): ?int
    {
        $eligible = $this->eligibleAgents($rule);
        if (empty($eligible)) {
            return null;
        }

        $counts = DB::table('support_tickets')
            ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
            ->whereIn('assign_to_id', $eligible)
            ->whereNotIn('default_status', [2, 3])
            ->groupBy('assign_to_id')
            ->pluck('active_count', 'assign_to_id')
            ->all();

        $sorted = collect($eligible)
            ->map(fn($id) => [
                'id' => $id,
                'count' => $counts[$id] ?? 0,
            ])
            ->sortBy(['count', 'id'])
            ->first();

        return $sorted['id'] ?? null;
    }

    /**
     * Assigns a ticket to a currently live agent using Round Robin or Least Loaded.
     * Fallbacks to standard Round Robin if no agents are live.
     */
    protected function liveAgentAssign(AssignmentRule $rule): ?int
    {
        $allEligibleAgentIds = $this->eligibleAgents($rule);
        generateLogs("info", "All eligible agents: " . json_encode($allEligibleAgentIds));

        if (empty($allEligibleAgentIds)) {
            generateLogs("info", "No eligible agents found for rule.");
            return null;
        }

        // // 1. Find agents that are currently logged in (Live)
        // $liveAgentIds = DB::table('live_agents')
        //     ->whereIn('user_id', $allEligibleAgentIds)
        //     ->pluck('user_id')
        //     ->all();

        // // Optional: Apply 'agent_last_activity' threshold (if defined in rule parameters)
        // $params = $rule->parameters['live_agent'] ?? [];
        // if (isset($params['activity_threshold_minutes'])) {
        //     $threshold = now()->subMinutes($params['activity_threshold_minutes']);

        //     $liveAgentIds = DB::table('live_agents')
        //         ->whereIn('user_id', $liveAgentIds)
        //         ->where('agent_last_activity', '>', $threshold)
        //         ->pluck('user_id')
        //         ->all();

        //     generateLogs("info", "Live agents after activity filter: " . json_encode($liveAgentIds));
        // }

        // --- Step 1: Find agents that are currently logged in (Live) ---
        $params = $rule->parameters['live_agent'] ?? [];

        // Start with all agents in the 'live_agents' table
        $liveAgentsQuery = DB::table('live_agents')
            ->whereIn('user_id', $allEligibleAgentIds);

        // --- Step 2: Conditionally apply 'agent_last_activity' threshold ---
        if (isset($params['activity_threshold_minutes']) && $params['activity_threshold_minutes'] > 0) {
            $minutes = $params['activity_threshold_minutes'];
            $threshold = now()->subMinutes($minutes);

            // Only include live agents whose last activity is within the specified time
            $liveAgentsQuery->where('agent_last_activity', '>', $threshold);

            generateLogs("info", "Filtering live agents by activity threshold: last " . $minutes . " minutes.");
        }

        $liveAgentIds = $liveAgentsQuery->pluck('user_id')->all();
        generateLogs("info", "Final filtered live agents: " . json_encode($liveAgentIds));

        // 2. Check for fallback condition
        if (empty($liveAgentIds)) {
            generateLogs("warning", "No live agents found. Falling back to standard round_robin.");
            // Fallback to standard round_robin on ALL eligible agents
            return $this->roundRobinAssign($rule);
        }

        // 3. Determine the assignment method to use on Live Agents
        $liveMethod = $params['assignment_method'] ?? 'round_robin';

        $agentId = null;

        if ($liveMethod === 'least_loaded') {
            generateLogs("info", "Applying Least Loaded to Live Agents.");
            // We use a custom function for least loaded on a predefined set of IDs
            $agentId = $this->leastLoadedOnSet($liveAgentIds);
        } else {
            // Default to round_robin on live agents (requires transaction and parameter update)
            generateLogs("info", "Applying Round Robin to Live Agents.");
            $agentId = $this->roundRobinOnSet($liveAgentIds, $rule);
        }

        return $agentId;
    }

    protected function randomAssign(AssignmentRule $rule): ?int
    {
        $eligible = $this->eligibleAgents($rule);

        if (empty($eligible)) {
            return null;
        }

        // Randomly select one agent ID from the array
        $agentId = $eligible[array_rand($eligible)];

        generateLogs("info", "Returning random agent " . json_encode($agentId));
        return $agentId;
    }

    protected function eligibleAgents(AssignmentRule $rule): array
    {
        generateLogs("info", "Inside eligble Agent function " . json_encode($rule));
        // For now: pick agents in same department → via user_groups
        $query = Users::query()->where('role_id', 3)->where('is_delete', 'No');
        generateLogs("info", "Running query");
        if ($rule->department_id) {
            generateLogs("info", "in if statement");
            $query->whereHas('usergroup', fn($q) => $q->where('department_id', $rule->department_id));
        }
        generateLogs("info", "after if statement");
        return $query->pluck('id')->all();
    }

    /**
     * Finds the least loaded agent from a specific array of IDs.
     */
    protected function leastLoadedOnSet(array $agentIds): ?int
    {
        if (empty($agentIds)) {
            return null;
        }

        $counts = DB::table('support_tickets')
            ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
            ->whereIn('assign_to_id', $agentIds)
            // Use your existing logic for active tickets (default_status NOT IN [2, 3])
            ->whereNotIn('default_status', [2, 3])
            ->groupBy('assign_to_id')
            ->pluck('active_count', 'assign_to_id')
            ->all();

        $sorted = collect($agentIds)
            ->map(fn($id) => [
                'id' => $id,
                'count' => $counts[$id] ?? 0,
            ])
            // Sort by count (ascending) then by ID (ascending for tie-breaking)
            ->sortBy(['count', 'id'])
            ->first();

        return $sorted['id'] ?? null;
    }

    protected function roundRobinOnSet(array $agentIds, AssignmentRule $rule): ?int
    {
        return DB::transaction(function () use ($agentIds, $rule) {

            if (empty($agentIds)) {
                return null;
            }

            // Note: We use 'live_agent' key for parameter storage instead of 'round_robin'
            $params = $rule->parameters ?? [];
            $liveAgentParams = $params['live_agent'] ?? [];

            $lastId = $liveAgentParams['last_agent_id'] ?? null;

            // Sort the live agents by ID to ensure a stable rotation order
            $sorted = collect($agentIds)->sort()->values();

            $nextIdx = 0;
            if ($lastId && ($idx = $sorted->search($lastId)) !== false) {
                // Find the index of the last agent, and move to the next one
                $nextIdx = ($idx + 1) % $sorted->count();
            }

            $nextAgent = $sorted[$nextIdx];

            // update cursor in JSON, under the 'live_agent' key
            $params['live_agent']['last_agent_id'] = $nextAgent;
            $rule->parameters = $params;
            $rule->save();

            generateLogs("info", "Returning next live round robin agent " . json_encode($nextAgent));
            return $nextAgent;
        });
    }

    protected function assign(
        int $agentId,
        string $method,
        SupportTicket $ticket,
        ?int $ruleId = null,
        ?string $reason = null
    ): void {
        $ticket->assign_to_id = $agentId;
        $ticket->save();

        AssignmentHistory::create([
            'ticket_id' => $ticket->id,
            'assigned_to_id' => $agentId,
            'assigned_by_id' => auth()->user()->id ?? null,
            'assignment_method' => $method,
            'rule_id' => $ruleId,
            'reason' => $reason,
        ]);
    }
}
