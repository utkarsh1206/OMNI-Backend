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
            $ticketTypeSummary = $tickets->groupBy(fn($ticket) => $ticket->ticketType?->type ?? '')
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
            $prioritySummary = $tickets->groupBy(fn($ticket) => $ticket->priority?->name ?? '')
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

            $statusSummary = $statuses->groupBy(fn($status) => $status->ticketStatus?->name ?? '')
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

    public function exportCsv(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $tickets = $query->get();

            $headers = ['Ticket ID', 'Customer Name', 'Ticket Type', 'Priority', 'Status', 'Created Date', 'Last Updated', 'Assigned Agent', 'Resolution Time', 'Comments', 'Escalation'];

            $csvData = $this->formatTickets($tickets, true);

            $callback = function () use ($headers, $csvData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers); // Write the header row
                foreach ($csvData as $row) {
                    fputcsv($file, $row); // Write data rows
                }
                fclose($file);
            };

            // Create a response to download the file
            return new StreamedResponse($callback, ResponseHTTP::HTTP_OK, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment;filename="ticket_report.csv"',
            ]);
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
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('ticket_id')) {
            $query->where('support_ticket_id', $request->input('ticket_id'));
        }

        if ($request->has('priority')) {
            $query->where('default_priority', $request->input('priority'));
        }

        if ($request->has('status')) {
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
                    $ticket->support_ticket_id,
                    $customerName,
                    $ticket->ticketType->type ?? '',
                    $ticket->priority->name ?? '',
                    $ticket->ticketStatus->name ?? '',
                    $ticket->created_at->format('Y-m-d H:i:s'),
                    $ticket->updated_at->format('Y-m-d H:i:s'),
                    $ticket->assignedAgent->username ?? '',
                    $resolveDateTime,
                    $ticket->description,
                    $ticket->is_escalated,
                ];
            } else {
                return [
                    'id' => $ticket->id,
                    'ticketId' => $ticket->support_ticket_id,
                    'customerName' => $customerName,
                    'ticketType' => $ticket->ticketType->type,
                    'priority' => $ticket->priority->name,
                    'status' => $ticket->ticketStatus->name ?? '',
                    'createdDate' => $ticket->created_at->format('Y-m-d H:i:s'),
                    'lastUpdated' => $ticket->updated_at->format('Y-m-d H:i:s'),
                    'assignedAgent' => $ticket->assignedAgent->username ?? '',
                    'resolutionTime' => $resolveDateTime,
                    'comments' => $ticket->description,
                    'escalation' => $ticket->is_escalated,
                ];
            }
        });
    }
}
