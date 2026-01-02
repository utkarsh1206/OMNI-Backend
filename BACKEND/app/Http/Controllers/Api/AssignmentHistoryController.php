<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AssignmentHistoryRepository;

class AssignmentHistoryController extends Controller
{
    private $assignmentHistoryRepository;

    public function __construct(AssignmentHistoryRepository $assignmentHistoryRepository)
    {
        $this->assignmentHistoryRepository = $assignmentHistoryRepository;
    }

    public function index(Request $request)
    {
        return $this->assignmentHistoryRepository->list($request);
    }

    public function show($id)
    {
        return $this->assignmentHistoryRepository->find($id);
    }

    public function byTicket($ticketId)
    {
        return $this->assignmentHistoryRepository->byTicket($ticketId);
    }
}
