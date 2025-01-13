<?php

namespace App\Observers;

use App\Models\Supportticketnotification;
use Carbon\Carbon;

class SupportTicketNotificationObserver
{
    /**
     * Handle the Supportticketnotification "created" event.
     */
    public function created(Supportticketnotification $supportticketnotification): void
    {

        // if($supportticketnotification->status == "Open")
        // {
            $tickets = Supportticketnotification::where("support_ticket_id", $supportticketnotification->support_ticket_id)
            ->whereNotIn("status", ["Resolved", "Closed"])
            ->get(); 
            if($tickets->count())
            {
                foreach ($tickets as $value) {
                    $currentTicketTime = Carbon::parse($supportticketnotification->updated_at);  // Time of the current record
                    $otherTicketTime = Carbon::parse($value->updated_at);  // Time of the ticket in the loop
                    // Calculate the time difference
                    $timeDifference = $currentTicketTime->diffForHumans($otherTicketTime);
                    // Example: Output the time difference
                    echo "Time difference between current ticket and ticket with ID {$value->id} is: " . $timeDifference . "\n";
                }
            }

        // }
      
    }

    /**
     * Handle the Suppoirtticketnotification "updated" event.
     */
    public function updated(Supportticketnotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the Supportticketnotification "deleted" event.
     */
    public function deleted(Supportticketnotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the Supportticketnotification "restored" event.
     */
    public function restored(Supportticketnotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the Supportticketnotification "force deleted" event.
     */
    public function forceDeleted(Supportticketnotification $supportticketnotification): void
    {
        //
    }
}
