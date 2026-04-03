<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class TicketReportRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function ticketReport(Request $request)
    {
        try {
            $query = SupportTicket::query()->with([
                'notifications' => function ($query) {
                    $query->select(['id', 'support_ticket_id', 'status', 'created_at', 'created_by']);
                },
                'assignedAgent' => function ($query) {
                    $query->select(['id', 'username']);
                },
                'priority' => function ($query) {
                    $query->select(['id', 'name']);
                },
                'ticketType' => function ($query) {
                    $query->select(['id', 'type']);
                },
                'ticketStatus' => function ($query) {
                    $query->select(['id', 'name']);
                }
            ]);

            // Select all required fields explicitly
            $query->select([
                'id',
                'support_ticket_id',
                'default_type',
                'default_priority',
                'default_status',
                'created_at',
                'updated_at',
                'description',
                'is_escalated',
                'assign_to_id'
            ]);

            // Paginate the results
            $perPage = $request->input('perPage', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $payload = $this->extractPayload($request);

            $query = $this->applyFilters($query, $request, $payload);

            $query->orderByDesc('id');

            if ($page == config('omni.ALL_PAGE')) {
                $tickets = $query->get();
                $formattedTickets = $this->formatTickets($tickets);
                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $formattedTickets,
                    trans('message.data_fetch')
                );
            } else {
                $tickets = $query->paginate($perPage);

                // Get the original paginator instance
                $formattedItems = $this->formatTickets($tickets->items());

                // Replace the original items with formatted ones while keeping pagination data
                $tickets->setCollection(collect($formattedItems));

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $tickets,
                    trans('message.data_fetch')
                );
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $mappedTickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Report : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function ticketTypeSummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);

            $query = $this->applyFilters(SupportTicket::with('ticketType'), $request, $payload);
            $tickets = $query->get();

            // Group by ticketType and calculate summary
            $ticketTypeSummary = $tickets->groupBy(fn($ticket) => $ticket->ticketType?->type ?? 'Unknown')
                ->map(function ($group, $ticketTypeName) {
                    return [
                        'Ticket Type' => $ticketTypeName,
                        'Total Tickets' => $group->count(),
                    ];
                })->values();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $ticketTypeSummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Type Summary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function prioritySummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::with('priority'), $request, $payload);

            // Get all filtered tickets with priority relationship
            $tickets = $query->get();

            // Group by priority name and count
            $prioritySummary = $tickets->groupBy(fn($ticket) => $ticket->priority?->name ?? 'Unknown')
                ->map(fn($group, $priorityName) => [
                    'Priority' => $priorityName,
                    'Total Tickets' => $group->count(),
                ])
                ->values();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $prioritySummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket prioritySummary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function statusSummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::with('ticketStatus'), $request, $payload);

            $statuses = $query->get();

            $statusSummary = $statuses->groupBy(fn($status) => $status->ticketStatus?->name ?? 'Unknown')
                ->map(fn($group, $statusName) => [
                    'Status' => $statusName,
                    'Total Tickets' => $group->count(),
                ])
                ->values();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $statusSummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Status Summary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function weekdaySummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $weekdaySummary = $query->select(
                DB::raw('DAYOFWEEK(support_tickets.created_at) AS weekday'),
                DB::raw('COUNT(*) AS total_tickets')
            )
                ->whereRaw('WEEK(support_tickets.created_at) = WEEK(CURRENT_DATE())')
                ->whereRaw('YEAR(support_tickets.created_at) = YEAR(CURRENT_DATE())')
                ->groupBy(DB::raw('DAYOFWEEK(support_tickets.created_at)'))
                ->get();

            $weekdayMapping = [
                1 => 'Sunday',
                2 => 'Monday',
                3 => 'Tuesday',
                4 => 'Wednesday',
                5 => 'Thursday',
                6 => 'Friday',
                7 => 'Saturday',
            ];

            $formattedWeekdaySummary = $weekdaySummary->map(function ($day) use ($weekdayMapping) {
                return [
                    'Weekday' => $weekdayMapping[$day->weekday] ?? 'Unknown',
                    'Total Tickets' => $day->total_tickets,
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formattedWeekdaySummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Weekday Summary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function exportCsv(Request $request, $connectionName = null)
    {
        try {
            $selectedColumns = $request->input('selected_columns', []);

            if (empty($selectedColumns)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'No columns selected for export'
                );
            }

            $availableColumns =  [
                'Ticket ID',
                'Customer Name',
                'Ticket Type',
                'Priority',
                'Status',
                'Created Date',
                'Last Updated',
                'Assigned Agent',
                'Resolution Time',
                'Comments',
                'Escalation',
            ];

            // Match selected columns with available columns
            $validColumns = array_values(array_intersect(
                $selectedColumns,
                $availableColumns
            ));

            // If nothing valid left
            if (empty($validColumns)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'No valid columns available for export'
                );
            }

            $headers = $validColumns;

            $payload = $this->extractPayload($request);

            $query = SupportTicket::on($connectionName)
                ->leftJoin('users as assigned_agents', 'assigned_agents.id', '=', 'support_tickets.assign_to_id')
                ->leftJoin('priorities', 'priorities.id', '=', 'support_tickets.default_priority')
                ->leftJoin('ticket_types', 'ticket_types.id', '=', 'support_tickets.default_type')
                // ->leftJoin('support_ticket_statuses', 'support_ticket_statuses.id', '=', 'support_tickets.default_status')
                ->leftJoin('support_ticket_statuses', function ($join) {
                    $join->on('support_ticket_statuses.id', '=', 'support_tickets.default_status')
                         ->where('support_ticket_statuses.status', 'active');
                })

                // Latest Resolve Notification
                ->leftJoin(DB::raw("
        (
            SELECT support_ticket_id, MAX(created_at) as resolve_time
            FROM support_ticket_notifications
            WHERE status = 'Resolve'
            GROUP BY support_ticket_id
        ) as resolved
        "), 'resolved.support_ticket_id', '=', 'support_tickets.id')

                // First Notification User (Customer)
                ->leftJoin(DB::raw("
            (
                SELECT stn.support_ticket_id, u.username as customer_name
                FROM support_ticket_notifications stn
                LEFT JOIN users u ON u.id = stn.created_by
                WHERE stn.id IN (
                    SELECT MIN(id)
                    FROM support_ticket_notifications
                    GROUP BY support_ticket_id
                )
            ) as customer
        "), 'customer.support_ticket_id', '=', 'support_tickets.id')

                ->select([
                    'support_tickets.id',
                    'support_tickets.support_ticket_id',
                    'support_tickets.description',
                    'support_tickets.is_escalated',
                    'support_tickets.created_at',
                    'support_tickets.updated_at',
                    'assigned_agents.username as assigned_agent',
                    'priorities.name as priority_name',
                    'ticket_types.type as ticket_type',
                    'support_tickets.default_status',
                    'support_ticket_statuses.id as joined_status_id',
                    'support_ticket_statuses.name as status_name',
                    'resolved.resolve_time',
                    'customer.customer_name'
                ]);

            $query = $this->applyFilters($query, $request, $payload);

            $totalRecords = (clone $query)->count();

            generateLogs('info', 'Ticket Export - Total records found: ' . $totalRecords);

            $url = exportCsvChunked(
                $query->orderByDesc('support_tickets.id'),
                $validColumns,
                function ($ticket) use ($validColumns) {

                    $rowData = [
                        'Ticket ID'        => $ticket->support_ticket_id,
                        'Customer Name'    => $ticket->customer_name ?? '',
                        'Ticket Type'      => $ticket->ticket_type ?? 'Unknown',
                        'Priority'         => $ticket->priority_name ?? 'Unknown',
                        'Status'           => $ticket->status_name ?: 'Unknown',
                        'Created Date'     => $ticket->created_at
                            ? Carbon::parse($ticket->created_at)->format('Y-m-d H:i:s')
                            : '',
                        'Last Updated'     => $ticket->updated_at
                            ? Carbon::parse($ticket->updated_at)->format('Y-m-d H:i:s')
                            : '',
                        'Assigned Agent'   => $ticket->assigned_agent ?? 'Unknown',
                        'Resolution Time'  => $ticket->resolve_time
                            ? Carbon::parse($ticket->resolve_time)->format('Y-m-d H:i:s')
                            : '',
                        'Comments'         => $ticket->description,
                        'Escalation'       => $ticket->is_escalated,
                    ];

                    return collect($validColumns)
                        ->map(fn($col) => $rowData[$col] ?? '-')
                        ->toArray();
                },
                'ticket_report'
            );

            generateLogs('info', "url of ticket report : " . $url);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                ['file_url' => $url],
                trans('message.report_generated')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Export CSV : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function applyFilters($query, Request $request, $payload = null)
    {
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'));
            $endDate = Carbon::parse($request->input('endDate'));
            $query->whereBetween('support_tickets.created_at', [$startDate, $endDate]);
        }

        if ($request->has('ticket_id')) {
            $query->where('support_ticket_id', $request->input('ticket_id'));
        }

        if ($request->has('priority') && $request->input('priority') !== '') {
            $query->where('default_priority', $request->input('priority'));
        }

        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('default_status', $request->input('status'));
        }

        if ($request->has('ticketType')) {
            $query->where('default_type', $request->input('ticketType'));
        }

        if ($payload && $payload['role_id'] == 3) {
            $query->whereHas('assignedAgent', function ($q) use ($payload) {
                $q->where('id', $payload['id']);
            });
        }

        return $query;
    }

    private function extractPayload(Request $request)
    {
        return extractPayload($request->bearerToken());
    }

    private function formatTickets($tickets, $forCsv = false)
    {
        return collect($tickets)->map(function ($ticket) use ($forCsv) {
            $resolveNotification = $ticket->notifications
                ->where('status', 'Resolve')
                ->sortByDesc('created_at')
                ->first();

            $resolveDateTime = $resolveNotification?->created_at->format('Y-m-d H:i:s') ?? '';

            $customerName = $ticket->notifications->first()?->user->username ?? '';

            if ($forCsv) {
                // Return ordered array matching CSV headers
                return [
                    'Ticket ID'        => $ticket->support_ticket_id,
                    'Customer Name'    => $customerName,
                    'Ticket Type'      => $ticket->ticketType?->type ?? 'Unknown',
                    'Priority'         => $ticket->priority?->name ?? 'Unknown',
                    'Status'           => $ticket->ticketStatus?->name ?? 'Unknown',
                    'Created Date'     => $ticket->created_at?->format('Y-m-d H:i:s'),
                    'Last Updated'     => $ticket->updated_at?->format('Y-m-d H:i:s'),
                    'Assigned Agent'   => $ticket->assignedAgent?->username ?? 'Unknown',
                    'Resolution Time'  => $resolveDateTime,
                    'Comments'         => $ticket->description,
                    'Escalation'       => $ticket->is_escalated,
                ];
            } else {
                return [
                    'id' => $ticket->id,
                    'ticketId' => $ticket->support_ticket_id,
                    'customerName' => $customerName,
                    'ticketType' => $ticket->ticketType->type ?? 'Unknown',
                    'priority' => $ticket->priority->name ?? 'Unknown',
                    'status' => $ticket->ticketStatus->name ?? 'Unknown',
                    'createdDate' => $ticket->created_at->format('Y-m-d H:i:s'),
                    'lastUpdated' => $ticket->updated_at->format('Y-m-d H:i:s'),
                    'assignedAgent' => $ticket->assignedAgent->username ?? 'Unknown',
                    'resolutionTime' => $resolveDateTime,
                    'comments' => $ticket->description,
                    'escalation' => $ticket->is_escalated,
                ];
            }
        });
    }
}
