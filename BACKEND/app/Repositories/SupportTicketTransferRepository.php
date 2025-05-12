<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketNotification;
use App\Models\SupportTicket;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

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
            $supportTicket = SupportTicket::where('support_ticket_id', $request->supportticket_id)->first();

            if (!$supportTicket) {
                return response()->json(['error' => 'Ticket not found'], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $supportTicket->assign_to_id = $request->user_id;
            $supportTicket->save();

            SupportTicketNotification::create([
                'type' => "Transfer",
                'status' => $supportTicket->default_status,
                'comment' => $request->comment,
                'support_ticket_id' => $supportTicket->id,
                'date' => gmdate('Y-m-d H:i:s'),
                'icon' =>  getIconforNotification('Ticket Transfer'),
                'color' =>  getColorforNotification('Ticket Transfer'),
                'created_by' => auth()->user()->id,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.transfer_ticket'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Support Ticket Transfer : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
