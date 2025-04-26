<?php

namespace App\Observers;

use App\Models\LiveAgents;
use App\Events\AgentLiveStatus;

class LiveAgentObserver
{
    /**
     * Handle the SupportTicketNotification "created" event.
     */
    public function created(LiveAgents $liveAgent): void
    {
        
    }

    /**
     * Handle the Suppoirtticketnotification "updated" event.
     */
    public function updated(LiveAgents $liveAgent): void
    {
        broadcast(new AgentLiveStatus($liveAgent));
    }

    /**
     * Handle the SupportTicketNotification "deleted" event.
     */
    public function deleted(LiveAgents $liveAgent): void
    {
        //
    }

    /**
     * Handle the SupportTicketNotification "restored" event.
     */
    public function restored(LiveAgents $liveAgent): void
    {
        //
    }

    /**
     * Handle the SupportTicketNotification "force deleted" event.
     */
    public function forceDeleted(LiveAgents $liveAgent): void
    {
        //
    }
}
