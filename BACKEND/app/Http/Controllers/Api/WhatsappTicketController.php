<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsappTicketRequest;
use App\Repositories\WhatsappTicketRepository;
use Illuminate\Http\JsonResponse;

class WhatsappTicketController extends Controller
{
    protected $repository;

    public function __construct(WhatsappTicketRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(WhatsappTicketRequest $request): JsonResponse
    {
        return $this->repository->createWhatsAppTicket($request);
    }
}
