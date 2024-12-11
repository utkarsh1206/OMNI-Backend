<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicketnotification;
use Illuminate\Http\Request;

class SupportticketnotificationController extends Controller
{
    public function index(Request $request)
    {
        // Get the support_ticket_id from the query string (optional, default to null if not provided)
        $supportTicketId = $request->query('supportticket_id');                
        // Fetch notifications based on the support_ticket_id and order by date in descending order
        $notifications = SupportTicketnotification::where('support_ticket_id', $supportTicketId)
            ->orderBy('date', 'desc')
            ->get();

        // Return the response in JSON format
        return response()->json([
            'data' => $notifications
        ], 200); // Status code 200 for success
    }
}
