<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\IncidentType;
use App\Models\Priority;
use App\Models\SlaTarget;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\DB;
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

            $ticketsQuery = SupportTicket::with([
                'notifications' => function ($query) {
                    $query->select(
                        'support_ticket_id',
                        DB::raw("MAX(CASE WHEN status = 'Resolved' THEN date ELSE NULL END) as resolved_date")
                    )->groupBy('support_ticket_id');
                },
                'priority',
                'ticketType'
            ])
                ->select(
                    'support_tickets.id',
                    'support_tickets.support_ticket_id as ticket_id',
                    'support_tickets.default_type',
                    'support_tickets.default_priority',
                    'support_tickets.created_at as opened_date',
                    'support_tickets.is_escalated as sla_compliance',
                    'support_tickets.actual_resolved_time',
                    'support_tickets.incident_id'
                )
                ->join('users', 'support_tickets.assign_to_id', '=', 'users.id')
                ->addSelect('users.username as user_name');

            $payload = $this->extractPayload($request);

            $ticketsQuery = $this->applyFilters($ticketsQuery, $request, $payload);

            if ($page == config('omni.ALL_PAGE')) {
                $tickets = $ticketsQuery->get();

                // Process all records
                $incidentIds = $tickets->pluck('incident_id')->unique();
                $incidentSLA = IncidentType::whereIn('id', $incidentIds)
                    ->pluck('sla_id', 'id');

                $slaIds = $incidentSLA->values()->unique();
                $slaTargets = SlaTarget::whereIn('sla_id', $slaIds)
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->sla_id . '_' . $item->priority_type;
                    });

                $formattedTickets = $tickets->map(function ($ticket) use ($incidentSLA, $slaTargets) {
                    return $this->formatTicketWithSLA($ticket, $incidentSLA, $slaTargets);
                });

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $formattedTickets,
                    trans('message.data_fetch')
                );
            } else {
                // Get paginated results first
                $paginatedTickets = $ticketsQuery->paginate($perPage);

                // Process only the current page items
                $incidentIds = $paginatedTickets->getCollection()->pluck('incident_id')->unique();
                $incidentSLA = IncidentType::whereIn('id', $incidentIds)
                    ->pluck('sla_id', 'id');

                $slaIds = $incidentSLA->values()->unique();
                $slaTargets = SlaTarget::whereIn('sla_id', $slaIds)
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->sla_id . '_' . $item->priority_type;
                    });

                // Transform the collection while keeping pagination
                $transformedCollection = $paginatedTickets->getCollection()->map(function ($ticket) use ($incidentSLA, $slaTargets) {
                    return $this->formatTicketWithSLA($ticket, $incidentSLA, $slaTargets);
                });

                // Set the transformed collection back to the paginator
                $paginatedTickets->setCollection($transformedCollection);

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $paginatedTickets,
                    trans('message.data_fetch')
                );
            }
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla Report List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function formatTicketWithSLA($ticket, $incidentSLA, $slaTargets)
    {
        $slaId = $incidentSLA[$ticket->incident_id] ?? null;
        $resolveTime = $slaId ? ($slaTargets[$slaId . '_' . $ticket->priority] ?? null) : null;
        $resolveTimeMinutes = $resolveTime ? $resolveTime->first()->resolve_time_value : null;

        // Convert minutes to HH:MM:SS
        $resolveTimeFormatted = null;
        if ($resolveTimeMinutes !== null) {
            $hours = floor($resolveTimeMinutes / 60);
            $minutes = $resolveTimeMinutes % 60;
            $resolveTimeFormatted = sprintf('%02d:%02d:00', $hours, $minutes);
        }

        return [
            'id' => $ticket->id,
            'ticket_id' => $ticket->ticket_id,
            'ticketType' => $ticket->ticketType->type ?? '',
            'priority' => $ticket->priority->name ?? '',
            'opened_date' => $ticket->opened_date,
            'sla_compliance' => $ticket->sla_compliance,
            'user_name' => $ticket->user_name,
            'incident_id' => $ticket->incident_id,
            'resolve_time_value' => $resolveTimeFormatted,
            'actual_resolved_time' => $ticket->actual_resolved_time,
            'resolved_date' => $ticket->notifications->first()->resolved_date ?? null
        ];
    }

    public function prioritySLA(Request $request)
    {
        try {
            $tickets = SupportTicket::select(
                'priorities.name as priority',
                DB::raw("COUNT(*) as ticket_count")
            )
                ->join('priorities', 'support_tickets.default_priority', '=', 'priorities.id')
                ->where('support_tickets.is_escalated', 'Yes')
                ->groupBy('priorities.name');

            $payload = $this->extractPayload($request);

            $tickets = $this->applyFilters($tickets, $request, $payload);

            $tickets = $tickets->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $tickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla prioritySLA List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function ticketTypeSLA(Request $request)
    {
        try {
            $tickets = SupportTicket::select(
                'ticket_types.type as ticketType',
                DB::raw("COUNT(*) as ticket_count")
            )
                ->join('ticket_types', 'support_tickets.default_type', '=', 'ticket_types.id')
                ->where('support_tickets.is_escalated', 'Yes')
                ->groupBy('ticket_types.type');

            $payload = $this->extractPayload($request);

            $tickets = $this->applyFilters($tickets, $request, $payload);

            $tickets = $tickets->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $tickets, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Sla ticketTypeSLA List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function incidentSLA(Request $request)
    {
        try {
            $tickets = DB::table('support_tickets')
                ->join('incident_types', 'support_tickets.incident_id', '=', 'incident_types.id')
                ->select(
                    'support_tickets.incident_id',
                    'incident_types.name as incident_name',
                    DB::raw('COUNT(support_tickets.id) as ticket_count')
                )
                ->where('support_tickets.is_escalated', 'Yes')
                ->groupBy('support_tickets.incident_id', 'incident_types.name');

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
            $ticketsquery = SupportTicket::with([
                'notifications' => function ($query) {
                    $query->select(
                        'support_ticket_id',
                        DB::raw("MAX(CASE WHEN status = 'Resolved' THEN date ELSE NULL END) as resolved_date")
                    )->groupBy('support_ticket_id');
                },
                'priority',
                'ticketType'
            ])
                ->select(
                    'support_tickets.id',
                    'support_tickets.support_ticket_id as ticket_id',
                    'support_tickets.default_type',
                    'support_tickets.default_priority',
                    'support_tickets.created_at as opened_date',
                    'support_tickets.is_escalated as sla_compliance'
                )
                ->join('users', 'support_tickets.assign_to_id', '=', 'users.id')
                ->addSelect('users.username as user_name');

            $payload = $this->extractPayload($request);

            $ticketsquery = $this->applyFilters($ticketsquery, $request, $payload);
            $tickets = $ticketsquery->get();

            // Prepare CSV content
            $headers = ['User Name', 'Ticket ID', 'Ticket Type', 'Priority', 'Opened Date', 'Resolved Date', 'SLA Compliance'];

            $callback = function () use ($headers, $tickets) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);

                foreach ($tickets as $ticket) {
                    $resolvedDate = $ticket->notifications->first()->resolved_date ?? null;
                    $openedDate = Carbon::parse($ticket->opened_date)->toDateTimeString();
                    $row = [
                        $ticket->user_name,
                        $ticket->ticket_id,
                        $ticket->ticketType->type ?? '',
                        $ticket->priority->name ?? 'm',
                        $openedDate,
                        $resolvedDate ? Carbon::parse($resolvedDate)->toDateTimeString() : null,
                        $ticket->sla_compliance,
                    ];
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return new StreamedResponse($callback, ResponseHTTP::HTTP_OK, [
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
            $query->whereBetween('support_tickets.created_at', [$startDate, $endDate]);
        }

        if ($request->has('priority')) {
            $query->where('support_tickets.default_priority', $request->input('priority'));
        }

        if ($request->has('ticketType')) {
            $query->where('support_tickets.default_type', $request->input('ticketType'));
        }

        if ($request->has('slaCompliance')) {
            $query->where('support_tickets.is_escalated', $request->input('slaCompliance'));
        }

        if ($payload['role_id'] == 3) {
            $query->where('assign_to_id', $payload['id']);
        }

        if ($request->has('user')) {
            $query->where('support_tickets.assign_to_id', $request->input('user'));
        }

        return $query;
    }

    private function extractPayload(Request $request)
    {
        return extractPayload($request->bearerToken());
    }
}
