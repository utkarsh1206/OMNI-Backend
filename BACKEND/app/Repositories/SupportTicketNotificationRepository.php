<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\SupportTicketNotification;
use App\Http\Response\CustomApiResponse;
use Exception;

class SupportTicketNotificationRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $supportTicketId = $request->query('supportticket_id');
            $notifications = SupportTicketNotification::where('support_ticket_id', $supportTicketId)
                ->orderBy('created_at', 'desc')
                ->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $notifications, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Support Ticket Notification : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
