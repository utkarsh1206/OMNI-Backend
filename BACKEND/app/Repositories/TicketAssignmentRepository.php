<?php

namespace App\Repositories;

use App\Models\SupportTicket;
use App\Models\AssignmentHistory;
use App\Jobs\AssignTicketJob;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Auth;

class TicketAssignmentRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function manualAssign($ticketId, $request)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);
            $agentId = $request->input('agent_id');

            $ticket->assign_to_id = $agentId;
            $ticket->save();

            AssignmentHistory::create([
                'ticket_id' => $ticket->id,
                'assigned_to_id' => $agentId,
                'assigned_by_id' => Auth::id(),
                'assignment_method' => 'manual',
                'reason' => $request->input('reason', 'Manual assignment'),
            ]);

            return $this->apiResponse->success($ticket, 'Ticket manually assigned.');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function autoAssign($ticketId)
    {
        try {
            $ticket = SupportTicket::findOrFail($ticketId);

            AssignTicketJob::dispatch($ticket);

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
}
