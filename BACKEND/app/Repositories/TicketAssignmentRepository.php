<?php

namespace App\Repositories;

use App\Models\SupportTicket;
use App\Models\AssignmentHistory;
use App\Jobs\AssignTicketJob;
use App\Http\Response\CustomApiResponse;
use App\Models\AssignmentRule;
use App\Models\MailTemplate;
use App\Models\Users;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketAssignmentRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    /**
     * This function will be very much required
     * when we will be creating JOB for auto assign.
     */
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

    /**
     * This function can come in handy in the case
     * when we require to unassign a ticket
     */
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

    public function autoAssignApi(Request $request, $ticketId)
    {
        try {
            $payload = extractPayload($request->bearerToken());

            $ticket = SupportTicket::findOrFail($ticketId);

            $usergroupId = $request->input('usergroup_id');

            generateLogs("info", "inside the assign ticket job");

            // Skip if already assigned
            if ($ticket->assign_to_id && $ticket->assign_to_id > 1) {   // user_id = 1 is defined for Admin
                return;
            }

            // If created by agent → assign to same agent
            if ($payload['role_id'] && $payload['role_id'] === 3) {
                $this->assign(auth()->user()->id, 'self-asign', $ticket, null, "Ticket created by agent");
                return;
            }

            // Auto assignment via rules
            $rule = $this->findApplicableRule($ticket);
            generateLogs("info", "rule found " . json_encode($rule));
            if (!$rule) {
                $this->assign(0, 'unassigned', $ticket, null, 'No matching rule found');
                return;
            }

            $agentId = null;

            switch ($rule->method) {
                case 'round_robin':
                    generateLogs("info", "CASE Round Robin");
                    $agentId = $this->roundRobinAssign($rule, $usergroupId);
                    break;

                case 'least_loaded':
                    generateLogs("info", "CASE Least Loaded");
                    $agentId = $this->leastLoadedAssign($rule, $usergroupId);
                    break;

                case 'random':
                    generateLogs("info", "CASE Random");
                    $agentId = $this->randomAssign($rule, $usergroupId);
                    break;

                case 'live_agent':
                    generateLogs("info", "CASE Live Agent Assignment");
                    $agentId = $this->liveAgentAssign($rule, $usergroupId);
                    break;

                default:
                    generateLogs("info", "CASE Default (Random)");
                    $agentId = $this->randomAssign($rule, $usergroupId); // fallback to random
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

    protected function roundRobinAssign(AssignmentRule $rule, ?int $usergroupId = null): ?int
    {
        return DB::transaction(function () use ($rule, $usergroupId) {
            generateLogs("info", "inside round robin funciton");

            $params = $rule->parameters ?? [];
            generateLogs("info", "found params" . json_encode($params));

            $eligible = $this->eligibleAgents($rule, $usergroupId);
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

    protected function leastLoadedAssign(AssignmentRule $rule, ?int $usergroupId = null): ?int
    {
        $eligible = $this->eligibleAgents($rule, $usergroupId);
        generateLogs("info", "Eligible agents " . json_encode($eligible));
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
    protected function liveAgentAssign(AssignmentRule $rule, ?int $usergroupId = null): ?int
    {
        $allEligibleAgentIds = $this->eligibleAgents($rule, $usergroupId);
        generateLogs("info", "All eligible agents: " . json_encode($allEligibleAgentIds));

        if (empty($allEligibleAgentIds)) {
            generateLogs("info", "No eligible agents found for rule.");
            return null;
        }

        $params = $rule->parameters['live_agent'] ?? [];

        $liveAgentsQuery = DB::table('live_agents')
            ->whereIn('user_id', $allEligibleAgentIds);

        if (isset($params['activity_threshold_minutes']) && $params['activity_threshold_minutes'] > 0) {
            $minutes = $params['activity_threshold_minutes'];
            $threshold = now()->subMinutes($minutes);

            $liveAgentsQuery->where('agent_last_activity', '>', $threshold);

            generateLogs("info", "Filtering live agents by activity threshold: last " . $minutes . " minutes.");
        }

        $liveAgentIds = $liveAgentsQuery->pluck('user_id')->all();
        generateLogs("info", "Final filtered live agents: " . json_encode($liveAgentIds));

        if (empty($liveAgentIds)) {
            generateLogs("warning", "No live agents found. Falling back to standard round_robin.");
            return $this->roundRobinAssign($rule);
        }

        $liveMethod = $params['assignment_method'] ?? 'round_robin';

        $agentId = null;

        if ($liveMethod === 'least_loaded') {
            generateLogs("info", "Applying Least Loaded to Live Agents.");
            $agentId = $this->leastLoadedOnSet($liveAgentIds);
        } else {
            generateLogs("info", "Applying Round Robin to Live Agents.");
            $agentId = $this->roundRobinOnSet($liveAgentIds, $rule);
        }

        return $agentId;
    }

    protected function randomAssign(AssignmentRule $rule, ?int $usergroupId = null): ?int
    {
        $eligible = $this->eligibleAgents($rule, $usergroupId);
        generateLogs("info", "Eligible agents " . json_encode($eligible));
        if (empty($eligible)) {
            return null;
        }

        $agentId = $eligible[array_rand($eligible)];

        generateLogs("info", "Returning random agent " . json_encode($agentId));
        return $agentId;
    }

    protected function eligibleAgents(AssignmentRule $rule, ?int $usergroupId = null): array
    {
        generateLogs("info", "Inside eligble Agent function " . json_encode($rule));

        $query = Users::query()->where('role_id', 3)->where('is_delete', 'No');

        if ($usergroupId) {
            $query->whereHas('usergroup', fn($q) => $q->where('id', $usergroupId));
        } elseif ($rule->department_id) {
            $query->whereHas('usergroup', fn($q) => $q->where('department_id', $rule->department_id));
        }

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
            ->whereNotIn('default_status', [2, 3])
            ->groupBy('assign_to_id')
            ->pluck('active_count', 'assign_to_id')
            ->all();

        $sorted = collect($agentIds)
            ->map(fn($id) => [
                'id' => $id,
                'count' => $counts[$id] ?? 0,
            ])
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

            $params = $rule->parameters ?? [];
            $liveAgentParams = $params['live_agent'] ?? [];

            $lastId = $liveAgentParams['last_agent_id'] ?? null;

            $sorted = collect($agentIds)->sort()->values();

            $nextIdx = 0;
            if ($lastId && ($idx = $sorted->search($lastId)) !== false) {
                $nextIdx = ($idx + 1) % $sorted->count();
            }

            $nextAgent = $sorted[$nextIdx];

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

        $mailTemplate = MailTemplate::where('type', 'created_ticket')->where('status', 'Active')->where('mail_for', 'agent')->first();

        if ($mailTemplate) {
            $templateData = [
                '{assigned_user}' => $ticket->assigned_agent_name,
                '{ticket_number}' => $ticket->support_ticket_id,
                '{ticket_subject}' => $ticket->subject,
                '{ticket_status}' => $ticket->status_name,
                '{ticket_created}' => $ticket->created_at->timezone(config('app.timezone'))->format('d M Y, H:i:s A T'),
                '{ticket_description}' => $ticket->description
            ];

            $mailTemplate->subject = strtr($mailTemplate->subject, $templateData);
            $mailTemplate->body = strtr($mailTemplate->body, $templateData);
        }

        setEmailConfig();

        $sender_email = config('mail.from.address');
        $defaultSubject = "A new ticket " . $ticket->support_ticket_id . " has been created";

        sendProcessedEmail(
            to: $ticket->user_email,
            subject: $mailTemplate['subject'] ?? $defaultSubject,
            bodyTemplate: $mailTemplate['body'] ?? '',
            mailTemplateId: $mailTemplate->id ?? 0,
            mailAttachments: [],
            ticket: $ticket,
            senderEmail: $sender_email,
            assignToId: $agentId,
            module: "Create Support Ticket"
        );

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
