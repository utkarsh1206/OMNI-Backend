<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendLeadMailRequest;
use App\Repositories\LeadMailRepository;

class LeadMailController extends Controller
{
    private $leadMailRepository;

    public function __construct(LeadMailRepository $leadMailRepository)
    {
        $this->leadMailRepository = $leadMailRepository;
    }

    /**
     * Send lead mail using template_id
     */
    public function send(SendLeadMailRequest $request, $template_id)
    {
        return $this->leadMailRepository->sendMail($request, $template_id);
    }
}
