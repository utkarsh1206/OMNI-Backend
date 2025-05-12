<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketNotification;
use App\Models\MailDetail;
use App\Models\SupportTicket;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

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
            // SupportTicket::where('id', $request->merge_supportticket_id)
            //     ->update(['default_status' => 'Closed']);
            // $SupportTicket = SupportTicket::where('id', $request->merge_supportticket_id)->get();

            // MailDetail::where('supportticket_id', $request->merge_supportticket_id)
            //     ->update(['supportticket_id' => $request->support_ticket_id]);
            // SupportTicketNotification::create([
            //     'type' => "Merged",
            //     'comment' => "Ticket merged with " . $SupportTicket->first()->support_ticket_id,
            //     'support_ticket_id' => $request->support_ticket_id,
            //     'date' => gmdate('Y-m-d H:i:s'),
            // ]);

            $mergedTicketId = $request->merge_supportticket_id;
            $targetTicketId = $request->support_ticket_id;

            // Fetch the ticket to be merged
            $mergedTicket = SupportTicket::where('support_ticket_id', $mergedTicketId)->first();
            generateLogs('info', 'Merge Ticket ' . json_encode($mergedTicket));
            $targetTicketData = SupportTicket::where('support_ticket_id', $targetTicketId)->first();
            generateLogs('info', 'Target Ticket ' . json_encode($targetTicketData));

            if ($mergedTicket->default_status == 'Closed') {
                return response()->json([
                    'error' => "Closed Ticket can't be merged",
                ], ResponseHTTP::HTTP_BAD_REQUEST);
            }
            if (!$mergedTicket) {
                throw new \Exception("Merged ticket not found.");
            }

            // Update its status to Closed
            $mergedTicket->default_status = 'Closed';
            $mergedTicket->save();

            // Reassign all related mail details to the new ticket
            MailDetail::where('supportticket_id', $mergedTicketId)
                ->update(['supportticket_id' => $targetTicketId]);

            // Create a merge notification on the target ticket
            SupportTicketNotification::create([
                'type' => 'Merged',
                'comment' => "Ticket merged with " . $mergedTicket->support_ticket_id,
                'support_ticket_id' => $targetTicketData->id,
                'date' => now()->toDateTimeString(),
                'icon' =>  getIconforNotification('Ticket Merge'),
                'color' =>  getColorforNotification('Ticket Merge'),
                'status' => $targetTicketData->default_status,
                'created_by' => auth()->user()->id,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.ticket_merge'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Support Ticket Merge : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
