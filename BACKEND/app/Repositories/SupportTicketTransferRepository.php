<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketnotification;
use App\Models\Supportticket;
use Exception;

class SupportTicketTransferRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }
    
    public function store(Request $request)
    {
        try {
            Supportticket::where('id', $request->supportticket_id)
            ->update(['assign_to_id' => $request->user_id]);
            SupportTicketNotification::create([
                'type' => "Transfer",
                'comment' => $request->comment,
                'support_ticket_id' => $request->supportticket_id,
                'date' => gmdate('Y-m-d H:i:s'),
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.transfer_ticket'));
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
