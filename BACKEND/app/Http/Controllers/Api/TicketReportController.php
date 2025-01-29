<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketReportController extends Controller
{
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

        if ($payload && $payload['role_id'] !== 0) {
            $query->whereHas('assignedAgent', function ($q) use ($payload) {
                $q->where('id', $payload['id']);
            });
        }

        return $query;
    }

    private function extractPayload(Request $request)
    {
        $token = $request->bearerToken();
        return $token ? JWTAuth::setToken($token)->getPayload() : null;
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

    public function ticketReport(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $query = SupportTicket::with(['notifications', 'assignedAgent']);

        $payload = $this->extractPayload($request);

        $query = $this->applyFilters($query, $request, $payload);

        if ($page === 'all') {
            $tickets = $query->get();
            $mappedTickets = $this->formatTickets($tickets->items());
            return response()->json([
                'data' => $mappedTickets,
            ]);
        } else {
            $tickets = $query->paginate($perPage);

            $mappedTickets = $this->formatTickets($tickets->items());

            return response()->json([
                'data' => $mappedTickets,
                'meta' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ]);
        }
    }

    public function categorySummary(Request $request)
    {
        $payload = $this->extractPayload($request);
        $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

        $categorySummary = $query->select(
            'supportticket.default_type',
            DB::raw('COUNT(*) AS total_tickets'),
            DB::raw('SUM(CASE WHEN supportticket.default_status = "Resolved" THEN 1 ELSE 0 END) AS resolved'),
            DB::raw('SUM(CASE WHEN supportticket.default_status = "Open" THEN 1 ELSE 0 END) AS open'),
            DB::raw('SUM(CASE WHEN supportticket.default_status = "Closed" THEN 1 ELSE 0 END) AS closed'),
            DB::raw('SUM(CASE WHEN supportticket.default_status NOT IN ("Resolved", "Open", "Closed") THEN 1 ELSE 0 END) AS pending')
        )
            ->groupBy('supportticket.default_type')
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

        return response()->json([
            'success' => 'Success',
            'data' => $formattedCategorySummary,
        ], 200);
    }

    public function prioritySummary(Request $request)
    {
        $payload = $this->extractPayload($request);
        $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

        $prioritySummary = $query->select(
            'supportticket.default_priority',
            DB::raw('COUNT(*) AS Total_Tickets')
        )
            ->groupBy('supportticket.default_priority')
            ->get();

        $formattedPrioritySummary = $prioritySummary->map(function ($priority) {
            return [
                'Priority' => ucfirst($priority->default_priority),
                'Total Tickets' => $priority->Total_Tickets ?? 0,
            ];
        });

        return response()->json([
            'success' => 'Success',
            'data' => $formattedPrioritySummary,
        ], 200);
    }

    public function statusSummary(Request $request)
    {
        $payload = $this->extractPayload($request);
        $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

        $statusSummary = $query->select(
            'supportticket.default_status',
            DB::raw('COUNT(*) AS total_tickets')
        )
            ->groupBy('supportticket.default_status')
            ->get();

        $formattedStatusSummary = $statusSummary->map(function ($status) {
            return [
                'Status' => $status->default_status,
                'Total Tickets' => $status->total_tickets,
            ];
        });

        return response()->json([
            'success' => 'Success',
            'data' => $formattedStatusSummary,
        ], 200);
    }

    public function weekdaySummary(Request $request)
    {
        $payload = $this->extractPayload($request);
        $query = $this->applyFilters(SupportTicket::query(), $request, $payload);

        $weekdaySummary = $query->select(
            DB::raw('DAYOFWEEK(supportticket.created_at) AS weekday'),
            DB::raw('COUNT(*) AS total_tickets')
        )
            ->whereRaw('WEEK(supportticket.created_at) = WEEK(CURRENT_DATE())')
            ->whereRaw('YEAR(supportticket.created_at) = YEAR(CURRENT_DATE())')
            ->groupBy(DB::raw('DAYOFWEEK(supportticket.created_at)'))
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

        return response()->json([
            'success' => 'Success',
            'data' => $formattedWeekdaySummary,
        ], 200);
    }

    public function exportCsv(Request $request)
    {
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
        $response = new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="ticket_report.csv"',
        ]);

        return $response;
    }
}
