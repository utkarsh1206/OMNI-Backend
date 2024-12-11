<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicketnotification;
use App\Models\Supportticket;

use Illuminate\Http\Request;

class SupporttickettransferController extends Controller
{
   
    public function store(Request $request)
    {
        $affectedRows = Supportticket::where('id', $request->supportticket_id)->update(['assign_to_id' => $request->user_id]);       
        $notification = SupportTicketNotification::create([
            'type' => "Transfer",
            'comment' => $request->comment,
            'support_ticket_id' => $request->supportticket_id,
            'date' =>gmdate('Y-m-d H:i:s'),
        ]);
        return response()->json([
            'message' => 'Transfer ticket successfully.',
        ], 201);
    }
}
