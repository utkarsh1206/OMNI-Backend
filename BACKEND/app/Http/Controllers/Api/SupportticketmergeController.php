<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicketnotification;
use App\Models\MailDetail;
use App\Models\Supportticket;
use Illuminate\Http\Request;

class SupportticketmergeController extends Controller
{
    public function store(Request $request)
    {
        $affectedRows = Supportticket::where('id', $request->merge_supportticket_id)->update(['default_status' => 'closed']);
        $Supportticket = Supportticket::where('id', $request->merge_supportticket_id)->get();


        $affectedmailRows = MailDetail::where('supportticket_id', $request->merge_supportticket_id)->update(['supportticket_id' =>$request->supportticket_id]);         
        $notification = SupportTicketNotification::create([
            'type' => "Merged",
            'comment' => "Ticket merged with ".$Supportticket->first()->support_ticket_id,
            'support_ticket_id' => $request->supportticket_id,
            'date' =>gmdate('Y-m-d H:i:s'),
        ]);
        return response()->json([
            'message' => 'Transfer ticket successfully.',
        ], 201);
    }
}
