<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketnotification;
use App\Models\MailDetail;
use App\Models\Supportticket;
use Exception;

class SupportTicketMergeRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store(Request $request)
    {
        try {
            Supportticket::where('id', $request->merge_supportticket_id)
            ->update(['default_status' => 'Closed']);
            $Supportticket = Supportticket::where('id', $request->merge_supportticket_id)->get();

            MailDetail::where('supportticket_id', $request->merge_supportticket_id)
            ->update(['supportticket_id' => $request->supportticket_id]);         
            SupportTicketNotification::create([
                'type' => "Merged",
                'comment' => "Ticket merged with ".$Supportticket->first()->support_ticket_id,
                'support_ticket_id' => $request->supportticket_id,
                'date' =>gmdate('Y-m-d H:i:s'),
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.ticket_merge'));
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
