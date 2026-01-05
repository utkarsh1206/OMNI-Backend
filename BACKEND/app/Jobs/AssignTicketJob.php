<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Models\AssignmentRule;
use App\Models\AssignmentHistory;
// use App\Models\User; // Assuming User model is aliased as User
use App\Models\Users; // Assuming Users is the model for agents (based on your repository file)
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignTicketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public SupportTicket $ticket;

    public function __construct(SupportTicket $ticket)
    {
        generateLogs("info", "AssignTicketJob: Initializing for Ticket ID: " . $ticket->id);
        $this->ticket = $ticket;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        generateLogs("info", "AssignTicketJob: Starting assignment process for Ticket ID: " . $this->ticket->id);

        // Case 0: Skip if already assigned to a non-Admin ID (Admin ID is assumed to be 1)
        if ($this->ticket->assign_to_id && $this->ticket->assign_to_id > 1) {
            generateLogs("info", "AssignTicketJob: Skipping. Ticket already assigned to agent ID: " . $this->ticket->assign_to_id);
            return;
        }

        // // Case 1: If created by agent → assign to same agent
        // // Assuming 'creator' relationship exists and role_id 3 is an agent
        // if (isset($this->ticket->creator) && $this->ticket->creator->role_id === 3) {
        //     $this->assign($this->ticket->creator->id, 'self_assign', $this->ticket, null, 'Created by agent');
        //     generateLogs("info", "AssignTicketJob: Self-assigned to creator agent ID: " . $this->ticket->creator->id);
        //     return;
        // }

        // Case 2: Auto assignment via rules
        $rule = $this->findApplicableRule($this->ticket);

        if (!$rule) {
            // Fallback: unassigned
            $this->assign(0, 'unassigned', $this->ticket, null, 'No matching rule found');
            generateLogs("warning", "AssignTicketJob: No rule found. Ticket ID: " . $this->ticket->id);
            return;
        }

        $agentId = null;
        generateLogs("info", "AssignTicketJob: Rule matched (ID: {$rule->id}, Method: {$rule->method})");

        switch ($rule->method) {
            case 'round_robin':
                $agentId = $this->roundRobinAssign($rule);
                break;

            case 'least_loaded':
                $agentId = $this->leastLoadedAssign($rule);
                break;

            case 'random':
                $agentId = $this->randomAssign($rule);
                break;

            case 'live_agent':
                $agentId = $this->liveAgentAssign($rule);
                break;

            default:
                // Fallback to round_robin
                $agentId = $this->roundRobinAssign($rule);
                break;
        }

        if ($agentId) {
            $this->assign($agentId, $rule->method, $this->ticket, $rule->id, "Auto-assigned by rule {$rule->id}");
            generateLogs("info", "AssignTicketJob: Successfully assigned Ticket ID: {$this->ticket->id} to Agent ID: {$agentId}");
        } else {
            $this->assign(0, 'unassigned', $this->ticket, $rule->id, 'No eligible agent found under rule');
            generateLogs("warning", "AssignTicketJob: No eligible agent found for Ticket ID: " . $this->ticket->id);
        }
    }

    /**
     * Finds the most applicable assignment rule.
     */
    protected function findApplicableRule(SupportTicket $ticket): ?AssignmentRule
    {
        // Using the logic from the repository: prioritize rules that match
        // EITHER incident OR department, ordered by priority_order.
        return AssignmentRule::query()
            ->where('enabled', true)
            ->where(function ($q) use ($ticket) {
                // Match by incident_id
                if ($ticket->incident_id) {
                    $q->where('incident_type_id', $ticket->incident_id);
                }
                // OR match by department_id
                if ($ticket->department_id) {
                    $q->orWhere('department_id', $ticket->department_id);
                }
            })
            ->orderByDesc('priority_order')
            ->first();
    }

    /**
     * Retrieves all eligible agents for a rule (based on department and role).
     */
    protected function eligibleAgents(AssignmentRule $rule): array
    {
        // Using Users model based on your repository file
        $query = Users::query()->where('role_id', 3)->where('is_delete', 'No');

        if ($rule->department_id) {
            // Assuming the 'usergroup' relationship links Users to departments
            $query->whereHas('usergroup', fn($q) => $q->where('department_id', $rule->department_id));
        }

        return $query->pluck('id')->all();
    }

    /**
     * Assigns using a Round Robin algorithm.
     */
    protected function roundRobinAssign(AssignmentRule $rule): ?int
    {
        return DB::transaction(function () use ($rule) {
            $params = $rule->parameters ?? [];
            $eligible = $this->eligibleAgents($rule);

            if (empty($eligible)) {
                return null;
            }

            $lastId = $params['round_robin']['last_agent_id'] ?? null;
            $sorted = collect($eligible)->sort()->values();

            $nextIdx = 0;
            if ($lastId && ($idx = $sorted->search($lastId)) !== false) {
                $nextIdx = ($idx + 1) % $sorted->count();
            }

            $nextAgent = $sorted->get($nextIdx);

            // update cursor in JSON
            $params['round_robin']['last_agent_id'] = $nextAgent;
            $rule->parameters = $params;
            $rule->save();

            return $nextAgent;
        });
    }

    /**
     * Assigns to the agent with the fewest currently active tickets.
     */
    protected function leastLoadedAssign(AssignmentRule $rule): ?int
    {
        $eligible = $this->eligibleAgents($rule);
        if (empty($eligible)) {
            return null;
        }

        // Count active tickets (where status is NOT 2 or 3) for the eligible agents
        $counts = DB::table('support_tickets')
            ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
            ->whereIn('assign_to_id', $eligible)
            ->whereNotIn('default_status', [2, 3])
            ->groupBy('assign_to_id')
            ->pluck('active_count', 'assign_to_id')
            ->all();

        // Map eligible agents to their count, defaulting to 0 for agents with no tickets
        $sorted = collect($eligible)
            ->map(fn($id) => [
                'id' => $id,
                'count' => $counts[$id] ?? 0,
            ])
            // Sort by count (least loaded first) then by ID (for stable tie-breaking)
            ->sortBy(['count', 'id'])
            ->first();

        return $sorted['id'] ?? null;
    }

    /**
     * Assigns randomly to an eligible agent.
     */
    protected function randomAssign(AssignmentRule $rule): ?int
    {
        $eligible = $this->eligibleAgents($rule);

        if (empty($eligible)) {
            return null;
        }

        // Randomly select one agent ID from the array
        return $eligible[array_rand($eligible)];
    }

    /**
     * Assigns to a currently "live" agent, falling back to standard RR/LL if none are live.
     */
    protected function liveAgentAssign(AssignmentRule $rule): ?int
    {
        $allEligibleAgentIds = $this->eligibleAgents($rule);

        if (empty($allEligibleAgentIds)) {
            return null;
        }

        $params = $rule->parameters['live_agent'] ?? [];

        // 1. Find agents in 'live_agents' table who are also eligible by rule
        $liveAgentsQuery = DB::table('live_agents')
            ->whereIn('user_id', $allEligibleAgentIds);

        // 2. Apply activity threshold filter if configured
        if (isset($params['activity_threshold_minutes']) && $params['activity_threshold_minutes'] > 0) {
            $minutes = $params['activity_threshold_minutes'];
            $threshold = now()->subMinutes($minutes);
            $liveAgentsQuery->where('agent_last_activity', '>', $threshold);
        }

        $liveAgentIds = $liveAgentsQuery->pluck('user_id')->all();

        // 3. Fallback: If no live agents found, default to standard round_robin on ALL eligible agents
        if (empty($liveAgentIds)) {
            generateLogs("warning", "AssignTicketJob: No live agents found for Ticket ID: {$this->ticket->id}. Falling back to standard round_robin.");
            return $this->roundRobinAssign($rule);
        }

        // 4. Determine assignment method for live agents
        $liveMethod = $params['assignment_method'] ?? 'round_robin';

        if ($liveMethod === 'least_loaded') {
            // Apply least loaded algorithm only on the subset of live agents
            return $this->leastLoadedOnSet($liveAgentIds);
        }

        // Default: Apply round robin on the subset of live agents
        return $this->roundRobinOnSet($liveAgentIds, $rule);
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
            ->whereNotIn('default_status', [2, 3])
            ->groupBy('assign_to_id')
            ->pluck('active_count', 'assign_to_id')
            ->all();

        $sorted = collect($agentIds)
            ->map(fn($id) => ['id' => $id, 'count' => $counts[$id] ?? 0])
            ->sortBy(['count', 'id'])
            ->first();

        return $sorted['id'] ?? null;
    }

    /**
     * Assigns using Round Robin on a specific array of IDs, updating the 'live_agent' rule parameters.
     */
    protected function roundRobinOnSet(array $agentIds, AssignmentRule $rule): ?int
    {
        return DB::transaction(function () use ($agentIds, $rule) {

            if (empty($agentIds)) {
                return null;
            }

            // Use 'live_agent' key for parameter storage
            $params = $rule->parameters ?? [];
            $liveAgentParams = $params['live_agent'] ?? [];

            $lastId = $liveAgentParams['last_agent_id'] ?? null;
            $sorted = collect($agentIds)->sort()->values();

            $nextIdx = 0;
            if ($lastId && ($idx = $sorted->search($lastId)) !== false) {
                $nextIdx = ($idx + 1) % $sorted->count();
            }

            $nextAgent = $sorted->get($nextIdx);

            // Update cursor in JSON, under the 'live_agent' key
            $params['live_agent']['last_agent_id'] = $nextAgent;
            $rule->parameters = $params;
            $rule->save();

            return $nextAgent;
        });
    }

    /**
     * Saves the assignment to the ticket and creates an AssignmentHistory record.
     */
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
