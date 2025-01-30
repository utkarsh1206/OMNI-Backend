<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supportticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SLAReportController extends Controller
{
    private function applyFilters($query, Request $request, $payload = null)
    {
        if ($request->has('startDate') && $request->has('endDate')) {
            $startDate = Carbon::parse($request->input('startDate'));
            $endDate = Carbon::parse($request->input('endDate'));
            $query->whereBetween('supportticket.created_at', [$startDate, $endDate]);
        }

        if ($request->has('priority')) {
            $query->where('supportticket.default_priority', $request->input('priority'));
        }

        if ($request->has('category')) {
            $query->where('supportticket.default_type', $request->input('category'));
        }

        if ($request->has('slaCompliance')) {
            $query->where('supportticket.is_escalated', $request->input('slaCompliance'));
        }

        if ($payload['role_id'] == 3) {
            $query->where('assign_to_id', $payload['id']);
        }

        if ($request->has('user')) {
            $query->where('supportticket.assign_to_id', $request->input('user'));
        }

        return $query;
    }

    private function extractPayload(Request $request)
    {
        $token = $request->bearerToken();
        return $token ? JWTAuth::setToken($token)->getPayload() : null;
    }

    public function generateReport(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $page = $request->input('page', 1);

        $ticketsquery = Supportticket::with([
            'notifications' => function ($query) {
                $query->select(
                    'support_ticket_id',
                    DB::raw("MAX(CASE WHEN status = 'Resolved' THEN date ELSE NULL END) as resolved_date")
                )->groupBy('support_ticket_id');
            },
        ])
            ->select(
                'supportticket.id',
                'supportticket.support_ticket_id as ticket_id',
                'supportticket.default_type as category',
                'supportticket.default_priority as priority',
                'supportticket.created_at as opened_date',
                'supportticket.is_escalated as sla_compliance'
            )
            ->join('users', 'supportticket.assign_to_id', '=', 'users.id')
            ->addSelect('users.username as user_name');

        $payload = $this->extractPayload($request);

        $ticketsquery = $this->applyFilters($ticketsquery, $request, $payload);

        if ($page === 'all') {
            $tickets = $ticketsquery->get();
            return response()->json([
                'data' => $tickets,
            ]);
        } else {
            $tickets = $ticketsquery->paginate($perPage);
            return response()->json([
                'data' => $tickets->items(),
                'meta' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'perPage' => $tickets->perPage(),
                    'total' => $tickets->total(),
                ],
            ]);
        }
    }

    public function prioritySLA(Request $request)
    {
        $tickets = Supportticket::select(
            'default_priority as priority',
            DB::raw("COUNT(*) as ticket_count")
        )
            ->where('is_escalated', 'Yes')
            ->groupBy('default_priority');

        $payload = $this->extractPayload($request);

        $tickets = $this->applyFilters($tickets, $request, $payload);

        $tickets = $tickets->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function categorySLA(Request $request)
    {
        $tickets = Supportticket::select(
            'default_type as category',
            DB::raw("COUNT(*) as ticket_count")
        )
            ->where('is_escalated', 'Yes')
            ->groupBy('default_type');

        $payload = $this->extractPayload($request);

        $tickets = $this->applyFilters($tickets, $request, $payload);

        $tickets = $tickets->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function incidentSLA(Request $request)
    {
        $tickets = DB::table('supportticket')
            ->join('incident_types', 'supportticket.incident_id', '=', 'incident_types.id')
            ->select(
                'supportticket.incident_id',
                'incident_types.name as incident_name',
                DB::raw('COUNT(supportticket.id) as ticket_count')
            )
            ->where('supportticket.is_escalated', 'Yes')
            ->groupBy('supportticket.incident_id', 'incident_types.name');

        $payload = $this->extractPayload($request);

        $tickets = $this->applyFilters($tickets, $request, $payload);

        $tickets = $tickets->get();

        return response()->json([
            'data' => $tickets,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $ticketsquery = Supportticket::with([
            'notifications' => function ($query) {
                $query->select(
                    'support_ticket_id',
                    DB::raw("MAX(CASE WHEN status = 'Resolved' THEN date ELSE NULL END) as resolved_date")
                )->groupBy('support_ticket_id');
            },
        ])
            ->select(
                'supportticket.id',
                'supportticket.support_ticket_id as ticket_id',
                'supportticket.default_type as category',
                'supportticket.default_priority as priority',
                'supportticket.created_at as opened_date',
                'supportticket.is_escalated as sla_compliance'
            )
            ->join('users', 'supportticket.assign_to_id', '=', 'users.id')
            ->addSelect('users.username as user_name');

        $payload = $this->extractPayload($request);

        $ticketsquery = $this->applyFilters($ticketsquery, $request, $payload);
        $tickets = $ticketsquery->get();

        // Prepare CSV content
        $headers = ['User Name', 'Ticket ID', 'Category', 'Priority', 'Opened Date', 'Resolved Date', 'SLA Compliance'];

        $callback = function () use ($headers, $tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($tickets as $ticket) {
                $resolvedDate = $ticket->notifications->first()->resolved_date ?? null;
                $openedDate = Carbon::parse($ticket->opened_date)->toDateTimeString();
                $row = [
                    $ticket->user_name,
                    $ticket->ticket_id,
                    $ticket->category,
                    $ticket->priority,
                    $openedDate,
                    $resolvedDate ? Carbon::parse($resolvedDate)->toDateTimeString() : null,
                    $ticket->sla_compliance,
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $response = new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;"',
        ]);

        return $response;
    }
}
