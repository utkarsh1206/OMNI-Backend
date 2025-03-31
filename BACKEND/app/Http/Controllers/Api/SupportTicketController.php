<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Services\SlaService;
use App\Repositories\SupportTicketRepository;
use App\Http\Requests\SupportTicketRequest;

class SupportTicketController extends Controller
{
    protected $slaService;

    private $slaReportRepository;

    // Constructor to inject the SlaService
    public function __construct(SlaService $slaService, SupportTicketRepository $slaReportRepository)
    {
        $this->slaService = $slaService;
        $this->slaReportRepository = $slaReportRepository;
    }

    public function index(Request $request)
    {
        return $this->slaReportRepository->list($request);
    }

    public function store(SupportTicketRequest $request)
    {
        return $this->slaReportRepository->store($request);
    }

    public function show(SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->show($supportTicket);
    }

    public function update(SupportTicketRequest $request, SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->update($request, $supportTicket);
    }

    public function statusUpdate(Request $request, SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->statusUpdate($request, $supportTicket);
    }

    public function destroy(SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->delete($supportTicket);
    }
}
