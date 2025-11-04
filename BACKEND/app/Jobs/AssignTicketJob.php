<?php

namespace App\Jobs;

use App\Models\SupportTicket;
use App\Models\AssignmentRule;
use App\Models\AssignmentHistory;
use App\Models\User;
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
        generateLogs("info", "Inside the constructor");
        $this->ticket = $ticket;
    }

    public function handle(): void
    {
        generateLogs("info", "inside the assign ticket job");
        // Skip if already assigned
        if ($this->ticket->assign_to_id && $this->ticket->assign_to_id > 0) {
            return;
        }

        // Case 1: If created by agent → assign to same agent
        if ($this->ticket->created_by && $this->ticket->creator->role_id === 3) {
            $this->assign($this->ticket->creator->id, 'self_assign');
            return;
        }

        // Case 2: Auto assignment via rules
        $rule = $this->findApplicableRule();

        if (!$rule) {
            // Fallback: unassigned
            $this->assign(0, 'unassigned', null, 'No matching rule found');
            return;
        }

        $agentId = null;

        switch ($rule->method) {
            case 'round_robin':
                $agentId = $this->roundRobinAssign($rule);
                break;

            case 'least_loaded':
                $agentId = $this->leastLoadedAssign($rule);
                break;

            default:
                $agentId = $this->roundRobinAssign($rule); // fallback to round_robin
                break;
        }

        if ($agentId) {
            $this->assign($agentId, $rule->method, $rule->id);
        } else {
            $this->assign(0, 'unassigned', $rule->id, 'No eligible agent found');
        }
    }

    protected function findApplicableRule(): ?AssignmentRule
    {
        return AssignmentRule::query()
            ->where(function ($q) {
                if ($this->ticket->incident_id) {
                    $q->where('incident_type_id', $this->ticket->incident_id);
                }
            })
            ->orWhere(function ($q) {
                if ($this->ticket->department_id) {
                    $q->where('department_id', $this->ticket->department_id);
                }
            })
            ->where('enabled', true)
            ->orderByDesc('priority_order')
            ->first();
    }

    /**
     * // Better rule than above as it checks for only department too,
     * // Also when there is incident_id, there will be department ID by default.
     * protected function findApplicableRule(): ?AssignmentRule
     * {
     * return AssignmentRule::query()
     *     ->where('enabled', true)
     *     ->where(function ($q) {
     *         if ($this->ticket->incident_id && $this->ticket->department_id) {
     *             // Match both department and incident
     *             $q->where('department_id', $this->ticket->department_id)
     *               ->where('incident_type_id', $this->ticket->incident_id);
     *         } elseif ($this->ticket->department_id) {
     *             // Fallback: match department only
     *             $q->where('department_id', $this->ticket->department_id);
     *         }
     *     })
     *     ->orderByDesc('priority_order')
     *     ->first();
     * }
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

            $nextAgent = $sorted[$nextIdx];

            // update cursor in JSON
            $params['round_robin']['last_agent_id'] = $nextAgent;
            $rule->parameters = $params;
            $rule->save();

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
     * protected function priorityBasedAssign(AssignmentRule $rule, SupportTicket $ticket): ?int
     * {
     *     $priority = $ticket->default_priority ?? 1;
     *     $minLevel = $this->getMinRequiredLevel($priority);
     * 
     *     $eligible = $this->eligibleAgentsByLevel($minLevel, $rule);
     * 
     *     return $this->leastLoadedAmong($eligible);
     * }
     * 
     * protected function getMinRequiredLevel(int $priority): int
     * {
     *     $allPriorities = DB::table('support_tickets')
     *         ->distinct()
     *         ->pluck('default_priority')
     *         ->sort()
     *         ->values();
     * 
     *     $count = $allPriorities->count();
     *     if ($count === 0) {
     *         return 1; // fallback
     *     }
     * 
     *     $bandSize = ceil($count / 3);
     * 
     *     $lowBand = $allPriorities->slice(0, $bandSize);
     *     $midBand = $allPriorities->slice($bandSize, $bandSize);
     *     $highBand = $allPriorities->slice($bandSize * 2);
     * 
     *     if ($highBand->contains($priority)) {
     *         return 3;
     *     } elseif ($midBand->contains($priority)) {
     *         return 2;
     *     } else {
     *         return 1;
     *     }
     * }
     * 
     * protected function eligibleAgentsByLevel(int $minLevel, AssignmentRule $rule): array
     * {
     *     return User::query()
     *         ->where('role_id', 3)
     *         ->where('is_delete', 'No')
     *         ->where('level', '>=', $minLevel)
     *         ->when($rule->department_id, fn($q) =>
     *             $q->whereHas('usergroup', fn($q2) =>
     *                 $q2->where('department_id', $rule->department_id)
     *             )
     *         )
     *         ->pluck('id')
     *         ->all();
     * }
     * 
     * protected function leastLoadedAmong(array $agentIds): ?int
     * {
     *     if (empty($agentIds)) {
     *         return null;
     *     }
     * 
     *     $counts = DB::table('support_tickets')
     *         ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
     *         ->whereIn('assign_to_id', $agentIds)
     *         ->whereNotIn('default_status', [2, 3]) // adjust if needed
     *         ->groupBy('assign_to_id')
     *         ->pluck('active_count', 'assign_to_id')
     *         ->all();
     * 
     *     $sorted = collect($agentIds)
     *         ->map(fn($id) => ['id' => $id, 'count' => $counts[$id] ?? 0])
     *         ->sortBy(['count', 'id'])
     *         ->first();
     * 
     *     return $sorted['id'] ?? null;
     * }
     */

    // Better approact to priority based assign
    // protected function priorityBasedAssign(AssignmentRule $rule, SupportTicket $ticket): ?int
    // {
    //     $priority = $ticket->default_priority ?? 1;

    //     // Step 1: Check if level mapping exists in parameters
    //     $params = $rule->parameters ?? [];
    //     $levelMap = $params['priority_level_map'] ?? [];

    //     // Step 2: If not mapped, compute and store it
    //     if (!array_key_exists($priority, $levelMap)) {
    //         $allPriorities = DB::table('support_tickets')
    //             ->distinct()
    //             ->pluck('default_priority')
    //             ->sort()
    //             ->values();

    //         $count = $allPriorities->count();
    //         $minLevel = 1;

    //         if ($count > 0) {
    //             $bandSize = ceil($count / 3);
    //             $lowBand = $allPriorities->slice(0, $bandSize);
    //             $midBand = $allPriorities->slice($bandSize, $bandSize);
    //             $highBand = $allPriorities->slice($bandSize * 2);

    //             if ($highBand->contains($priority)) {
    //                 $minLevel = 3;
    //             } elseif ($midBand->contains($priority)) {
    //                 $minLevel = 2;
    //             }
    //         }

    //         // Store the computed level in rule parameters
    //         $params['priority_level_map'][$priority] = $minLevel;
    //         $rule->parameters = $params;
    //         $rule->save();
    //     } else {
    //         $minLevel = $levelMap[$priority];
    //     }

    //     // Step 3: Get eligible agents based on level and department
    //     $eligible = User::query()
    //         ->where('role_id', 3)
    //         ->where('is_delete', 'No')
    //         ->where('level', '>=', $minLevel)
    //         ->when(
    //             $rule->department_id,
    //             fn($q) =>
    //             $q->whereHas(
    //                 'usergroup',
    //                 fn($q2) =>
    //                 $q2->where('department_id', $rule->department_id)
    //             )
    //         )
    //         ->pluck('id')
    //         ->all();

    //     if (empty($eligible)) {
    //         return null;
    //     }

    //     // Step 4: Load balancing — pick least-loaded agent
    //     $counts = DB::table('support_tickets')
    //         ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
    //         ->whereIn('assign_to_id', $eligible)
    //         ->whereNotIn('default_status', [2, 3]) // adjust if needed
    //         ->groupBy('assign_to_id')
    //         ->pluck('active_count', 'assign_to_id')
    //         ->all();

    //     $sorted = collect($eligible)
    //         ->map(fn($id) => ['id' => $id, 'count' => $counts[$id] ?? 0])
    //         ->sortBy(['count', 'id'])
    //         ->first();

    //     return $sorted['id'] ?? null;
    // }

    // protected function hybridAssign(AssignmentRule $rule, SupportTicket $ticket): ?int
    // {
    //     $priority = $ticket->default_priority ?? 1;
    //     $params = $rule->parameters ?? [];
    //     $levelMap = $params['priority_level_map'] ?? [];

    //     // Step 1: Compute and store level if missing
    //     if (!array_key_exists($priority, $levelMap)) {
    //         $allPriorities = DB::table('support_tickets')
    //             ->distinct()
    //             ->pluck('default_priority')
    //             ->sort()
    //             ->values();

    //         $count = $allPriorities->count();
    //         $minLevel = 1;

    //         if ($count > 0) {
    //             $bandSize = ceil($count / 3);
    //             $lowBand = $allPriorities->slice(0, $bandSize);
    //             $midBand = $allPriorities->slice($bandSize, $bandSize);
    //             $highBand = $allPriorities->slice($bandSize * 2);

    //             if ($highBand->contains($priority)) {
    //                 $minLevel = 3;
    //             } elseif ($midBand->contains($priority)) {
    //                 $minLevel = 2;
    //             }
    //         }

    //         $params['priority_level_map'][$priority] = $minLevel;
    //         $rule->parameters = $params;
    //         $rule->save();
    //     } else {
    //         $minLevel = $levelMap[$priority];
    //     }

    //     // Step 2: Get eligible agents
    //     $eligible = User::query()
    //         ->where('role_id', 3)
    //         ->where('is_delete', 'No')
    //         ->where('level', '>=', $minLevel)
    //         ->when(
    //             $rule->department_id,
    //             fn($q) =>
    //             $q->whereHas(
    //                 'usergroup',
    //                 fn($q2) =>
    //                 $q2->where('department_id', $rule->department_id)
    //             )
    //         )
    //         ->pluck('id')
    //         ->all();

    //     if (empty($eligible)) {
    //         return null;
    //     }

    //     // Step 3: Get load counts
    //     $counts = DB::table('support_tickets')
    //         ->select('assign_to_id', DB::raw('COUNT(*) as active_count'))
    //         ->whereIn('assign_to_id', $eligible)
    //         ->whereNotIn('default_status', [2, 3])
    //         ->groupBy('assign_to_id')
    //         ->pluck('active_count', 'assign_to_id')
    //         ->all();

    //     // Step 4: Find agents with minimum load
    //     $minLoad = collect($eligible)->map(fn($id) => $counts[$id] ?? 0)->min();

    //     $leastLoaded = collect($eligible)
    //         ->filter(fn($id) => ($counts[$id] ?? 0) === $minLoad)
    //         ->sort()
    //         ->values();

    //     if ($leastLoaded->isEmpty()) {
    //         return null;
    //     }

    //     // Step 5: Apply round-robin among least-loaded agents
    //     $lastId = $params['hybrid']['last_agent_id'] ?? null;
    //     $nextIdx = 0;

    //     if ($lastId && ($idx = $leastLoaded->search($lastId)) !== false) {
    //         $nextIdx = ($idx + 1) % $leastLoaded->count();
    //     }

    //     $nextAgent = $leastLoaded->get($nextIdx);

    //     // Step 6: Update round-robin cursor
    //     $params['hybrid']['last_agent_id'] = $nextAgent;
    //     $rule->parameters = $params;
    //     $rule->save();

    //     return $nextAgent;
    // }



    protected function eligibleAgents(AssignmentRule $rule): array
    {
        // For now: pick agents in same department → via user_groups
        $query = User::query()->where('role_id', 3)->where('is_delete', 'No');

        if ($rule->department_id) {
            $query->whereHas('usergroup', fn($q) => $q->where('department_id', $rule->department_id));
        }

        return $query->pluck('id')->all();
    }

    protected function assign(int $agentId, string $method, ?int $ruleId = null, ?string $reason = null): void
    {
        $this->ticket->assign_to_id = $agentId;
        $this->ticket->save();

        AssignmentHistory::create([
            'ticket_id' => $this->ticket->id,
            'assigned_to_id' => $agentId,
            'assigned_by_id' => auth()->id() ?? null,
            'assignment_method' => $method,
            'rule_id' => $ruleId,
            'reason' => $reason,
        ]);
    }
}
