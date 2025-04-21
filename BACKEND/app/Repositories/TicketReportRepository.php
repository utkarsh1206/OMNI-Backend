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
            $query = SupportTicket::with(['notifications', 'assignedAgent']);

            $payload = $this->extractPayload($request);

            $query = $this->applyFilters($query, $request, $payload);

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                // If 'page' is 'all', retrieve all records without pagination
                $tickets = $query->get();
                // $mappedTickets = $this->formatTickets($tickets->items());
                $mappedTickets = $this->formatTickets($tickets);
            } else {
                // Paginate the results
                $tickets = $query->paginate($perPage);

                $mappedTickets = $this->formatTickets($tickets->items());
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $mappedTickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Report : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function categorySummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $categorySummary = $query->select(
                'support_tickets.default_type',
                DB::raw('COUNT(*) AS total_tickets'),
                DB::raw('SUM(CASE WHEN support_tickets.default_status = "Resolved" THEN 1 ELSE 0 END) AS resolved'),
                DB::raw('SUM(CASE WHEN support_tickets.default_status = "Open" THEN 1 ELSE 0 END) AS open'),
                DB::raw('SUM(CASE WHEN support_tickets.default_status = "Closed" THEN 1 ELSE 0 END) AS closed'),
                DB::raw('SUM(CASE WHEN support_tickets.default_status NOT IN ("Resolved", "Open", "Closed") THEN 1 ELSE 0 END) AS pending')
            )
                ->groupBy('support_tickets.default_type')
                ->get();

            $formattedCategorySummary = $categorySummary->map(function ($category) {
                return [
                    'Category' => $category->default_type,
                    'Total Tickets' => $category->total_tickets,
                    'Open' => $category->open,
                    'Resolved' => $category->resolved,
                    'Pending' => $category->pending,
                    'Closed' => $category->closed,
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formattedCategorySummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Category Summary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function prioritySummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $prioritySummary = $query->select(
                'support_tickets.default_priority',
                DB::raw('COUNT(*) AS Total_Tickets')
            )
                ->groupBy('support_tickets.default_priority')
                ->get();

            $formattedPrioritySummary = $prioritySummary->map(function ($priority) {
                return [
                    'Priority' => ucfirst($priority->default_priority),
                    'Total Tickets' => $priority->Total_Tickets ?? 0,
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formattedPrioritySummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Category prioritySummary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function statusSummary(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $statusSummary = $query->select(
                'support_tickets.default_status',
                DB::raw('COUNT(*) AS total_tickets')
            )
                ->groupBy('support_tickets.default_status')
                ->get();

            $formattedStatusSummary = $statusSummary->map(function ($status) {
                return [
                    'Status' => $status->default_status,
                    'Total Tickets' => $status->total_tickets,
                ];
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $formattedStatusSummary, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during Ticket Category Status Summary : ', $ex->getMessage());
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
            generateLogs('error', 'Error during Ticket Category Weekday Summary : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function exportCsv(Request $request)
    {
        try {
            $payload = $this->extractPayload($request);
            $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

            $tickets = $query->get();

            $headers = ['Ticket ID', 'Customer Name', 'Category', 'Priority', 'Status', 'Created Date', 'Last Updated', 'Assigned Agent', 'Resolution Time', 'Comments', 'Escalation'];

            $csvData = $this->formatTickets($tickets);

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
            generateLogs('error', 'Error during Ticket Category Export CSV : ', $ex->getMessage());
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

        if ($request->has('category')) {
            $query->where('default_type', $request->input('category'));
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

    private function formatTickets($tickets)
    {
        return collect($tickets)->map(function ($ticket) {
            $resolveNotification = $ticket->notifications
                ->where('status', 'Resolved')
                ->sortByDesc('created_at')
                ->first();

            $resolveDateTime = $resolveNotification?->created_at->format('Y-m-d H:i:s') ?? '';

            $customerName = $ticket->notifications->first()?->user->username ?? '';

            return [
                'id' => $ticket->id,
                'ticketId' => $ticket->support_ticket_id,
                'customerName' => $customerName,
                'category' => $ticket->default_type,
                'priority' => $ticket->default_priority,
                'status' => $ticket->default_status,
                'createdDate' => $ticket->created_at->format('Y-m-d H:i:s'),
                'lastUpdated' => $ticket->updated_at->format('Y-m-d H:i:s'),
                'assignedAgent' => $ticket->assignedAgent->username ?? '',
                'resolutionTime' => $resolveDateTime,
                'comments' => $ticket->description,
                'escalation' => $ticket->is_escalated,
            ];
        });
    }
}
