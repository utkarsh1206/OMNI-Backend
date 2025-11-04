<?php

namespace App\Repositories;

use App\Models\AssignmentHistory;
use App\Http\Response\CustomApiResponse;
use Exception;

class AssignmentHistoryRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list($request)
    {
        try {
            $logs = AssignmentHistory::with(['ticket', 'agent', 'assignedBy', 'rule'])
                ->when($request->ticket_id, fn($q) => $q->where('ticket_id', $request->ticket_id))
                ->when($request->assigned_to_id, fn($q) => $q->where('assigned_to_id', $request->assigned_to_id))
                ->orderByDesc('created_at')
                ->paginate(20);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $logs, 'Assignment History Fetched');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function find($id)
    {
        try {
            $log = AssignmentHistory::with(['ticket', 'agent', 'rule'])->findOrFail($id);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $log, 'Assignment History Fetched.');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function byTicket($ticketId)
    {
        try {
            $logs = AssignmentHistory::where('ticket_id', $ticketId)
                ->with(['agent', 'rule'])
                ->orderByDesc('created_at')
                ->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $logs, 'Assignment History fetched by ticket.');
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
