<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\IncidentType;
use App\Models\SlaTarget;
use App\Models\Supportticket;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class SlaReportRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function generateReport(Request $request)
    {
        try {
            $perPage = $request->input('perPage', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

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
                    'supportticket.is_escalated as sla_compliance',
                    'supportticket.actual_resolved_time',
                    'supportticket.incident_id'
                )
                ->join('users', 'supportticket.assign_to_id', '=', 'users.id')
                ->addSelect('users.username as user_name');

            $payload = $this->extractPayload($request);

            $ticketsquery = $this->applyFilters($ticketsquery, $request, $payload);

            if ($page !== config('omni.ALL_PAGE')) {
                $ticketsquery = $ticketsquery->paginate($perPage);
            } else {
                $ticketsquery = $ticketsquery->get();
            }

            // Step 2: Get SLA IDs for incidents
            $incidentIds = $ticketsquery->pluck('incident_id')->unique();
            $incidentSLA = IncidentType::whereIn('id', $incidentIds)
                ->pluck('sla_id', 'id'); // ['incident_id' => sla_id]

            // Step 3: Get Resolve Time from SLA Targets
            $slaIds = $incidentSLA->values()->unique();
            $slaTargets = SlaTarget::whereIn('sla_id', $slaIds)
                ->get()
                ->groupBy(function ($item) {
                    return $item->sla_id . '_' . $item->priority_type;
                }); // Create a mapping ['slaId_priority' => resolve_time]

            // Step 4: Merge Data in PHP
            $ticketsquery->transform(function ($ticket) use ($incidentSLA, $slaTargets) {
                $slaId = $incidentSLA[$ticket->incident_id] ?? null;
                $resolveTime = $slaId ? ($slaTargets[$slaId . '_' . $ticket->priority] ?? null) : null;
                $resolveTimeMinutes = $resolveTime ? $resolveTime->first()->resolve_time_value : null;

                // Convert minutes to HH:MM:SS
                if ($resolveTimeMinutes !== null) {
                    $hours = floor($resolveTimeMinutes / 60);
                    $minutes = $resolveTimeMinutes % 60;
                    $seconds = 0; // Defaulting to 0 seconds
                    $ticket->resolve_time_value = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    $ticket->resolve_time_value = null;
                }

                return $ticket;
            });

            if ($page !== config('omni.ALL_PAGE')) {
                $ticketsquery = $ticketsquery->items();
            } else {
                $ticketsquery = $ticketsquery;
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $ticketsquery, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla Report List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
        // return response()->json([
        //     'data' => $page !== 'all' ? $ticketsquery->items() : $ticketsquery,
        //     'meta' => $page !== 'all' ? [
        //         'current_page' => $ticketsquery->currentPage(),
        //         'last_page' => $ticketsquery->lastPage(),
        //         'perPage' => $ticketsquery->perPage(),
        //         'total' => $ticketsquery->total(),
        //     ] : null,
        // ]);
    }

    public function prioritySLA(Request $request)
    {
        try {
            $tickets = Supportticket::select(
                'default_priority as priority',
                DB::raw("COUNT(*) as ticket_count")
            )
                ->where('is_escalated', 'Yes')
                ->groupBy('default_priority');

            $payload = $this->extractPayload($request);

            $tickets = $this->applyFilters($tickets, $request, $payload);

            $tickets = $tickets->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $tickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla prioritySLA List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function categorySLA(Request $request)
    {
        try {
            $tickets = Supportticket::select(
                'default_type as category',
                DB::raw("COUNT(*) as ticket_count")
            )
                ->where('is_escalated', 'Yes')
                ->groupBy('default_type');

            $payload = $this->extractPayload($request);

            $tickets = $this->applyFilters($tickets, $request, $payload);

            $tickets = $tickets->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $tickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla categorySLA List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function incidentSLA(Request $request)
    {
        try {
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $tickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla incidentSLA List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function exportCsv(Request $request)
    {
        try {
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

            return new StreamedResponse($callback,  ResponseHTTP::HTTP_OK, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment;"',
            ]);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla exportCsv List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

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
}
