<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketNotification;
use App\Models\SupportTicket;
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
            SupportTicket::where('id', $request->supportticket_id)
                ->update(['assign_to_id' => $request->user_id]);

            $supportTicket = SupportTicket::where('support_ticket_id', $request->supportticket_id)->first();

            generateLogs("info", "supportTicket data ", json_encode($supportTicket));
            if (!$supportTicket) {
                // Handle case where ticket isn't found
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            SupportTicketNotification::create([
                'type' => "Transfer",
                'status' => $supportTicket->default_status,
                'comment' => $request->comment,
                'support_ticket_id' => $supportTicket->id,
                'date' => gmdate('Y-m-d H:i:s'),
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.transfer_ticket'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Support Ticket Transfer : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
