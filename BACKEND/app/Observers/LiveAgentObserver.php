<?php

namespace App\Observers;

use App\Events\AgentForceLogout;
use App\Models\LiveAgents;
use App\Events\AgentLiveStatus;
use App\Models\LiveAgentDailyMetric;
use Illuminate\Support\Facades\DB;

class LiveAgentObserver
{
    /**
     * Handle the SupportTicketNotification "created" event.
     */
    public function created(LiveAgents $liveAgent): void
    {
        //
    }

    /**
     * Handle the Suppoirtticketnotification "updated" event.
     */
    public function updated(LiveAgents $liveAgent): void
    {

        $now = now();

        if ($liveAgent->isDirty('status')) {
            $previousStatus = $liveAgent->getOriginal('status');
            $lastUpdated = $liveAgent->status_last_updated_at ?? $now;

            if (strtolower($previousStatus) === 'pause') {
                $duration = $lastUpdated->lt($now) ? $lastUpdated->diffInSeconds($now) : 0;
                $liveAgent->pause_seconds += $duration;
            } elseif (strtolower($previousStatus) === 'resume') {
                $duration = $lastUpdated->lt($now) ? $lastUpdated->diffInSeconds($now) : 0;
                $liveAgent->ready_seconds += $duration;
            }

            $liveAgent->status_last_updated_at = $now;
        }


        if ($liveAgent->bypassObserver) {
            return;
        }

        broadcast(new AgentLiveStatus($liveAgent, $liveAgent->user_id));
    }

    /**
     * Handle the SupportTicketNotification "deleted" event.
     */
    public function deleted(LiveAgents $liveAgent): void
    {
        logUserLogoutActivity($liveAgent->user_id, $liveAgent->campaign_id);

        if ($liveAgent->bypassObserver ?? false) {
            return;
        }

        broadcast(new AgentForceLogout($liveAgent->user_id));
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

    public function saving(LiveAgents $liveAgent): void
    {
        $now = now();

        if ($liveAgent->isDirty('status')) {
            $previousStatus = $liveAgent->getOriginal('status');
            $lastUpdated = $liveAgent->status_last_updated_at ?? $now;

            if (strtolower($previousStatus) === 'pause') {
                $duration = $lastUpdated->lt($now) ? $lastUpdated->diffInSeconds($now) : 0;
                $liveAgent->pause_seconds += $duration;
            } elseif (strtolower($previousStatus) === 'resume') {
                $duration = $lastUpdated->lt($now) ? $lastUpdated->diffInSeconds($now) : 0;
                $liveAgent->ready_seconds += $duration;
            }

            $liveAgent->status_last_updated_at = $now;
        }

        if ($liveAgent->bypassObserver) {
            return;
        }
    }
}
