<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\TicketAssignmentRepository;

class TicketAssignmentController extends Controller
{
    private $ticketAssignmentRepository;

    public function __construct(TicketAssignmentRepository $ticketAssignmentRepository)
    {
        $this->ticketAssignmentRepository = $ticketAssignmentRepository;
    }

    public function assign(Request $request, $ticketId)
    {
        return $this->ticketAssignmentRepository->manualAssign($ticketId, $request);
    }

    public function autoAssign($ticketId)
    {
        return $this->ticketAssignmentRepository->autoAssign($ticketId);
    }

    public function unassign($ticketId)
    {
        return $this->ticketAssignmentRepository->unassign($ticketId);
    }
}
