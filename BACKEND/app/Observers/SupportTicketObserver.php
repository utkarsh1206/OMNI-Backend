<?php

namespace App\Observers;

use App\Models\Supportticket;
use App\Services\SlaService;


class SupportTicketObserver
{
    /**
     * Handle the Supportticket "created" event.
     */
    public function created(Supportticket $supportticket): void
    {
        if ($supportticket->default_status === 'Open') {
            $slaService = new SlaService();
            $slaData = $slaService->calculateSla($supportticket);
            $supportticket->update([
                'first_response_time' => $slaData['first_response_time'],
                'alert_before_response_notification_time' => $slaData['alert_before_response_notification_time'],
                'resolved_time' => $slaData['resolved_time'],
                'alert_before_resolve_notification_time' => $slaData['alert_before_resolve_notification_time'],
            ]);
        }
        else {
            $supportticket->update([
                'first_response_time' => null,
                'alert_before_response_notification_time' => null,
                'resolved_time' => null,
                'alert_before_resolve_notification_time' => null,
            ]);
        }
    }

    /**
     * Handle the Supportticket "updated" event.
     */
    public function updated(Supportticket $supportticket): void
    {
    }

    /**
     * Handle the Supportticket "deleted" event.
     */
    public function deleted(Supportticket $supportticket): void
    {
        //
    }

    /**
     * Handle the Supportticket "restored" event.
     */
    public function restored(Supportticket $supportticket): void
    {
        //
    }

    /**
     * Handle the Supportticket "force deleted" event.
     */
    public function forceDeleted(Supportticket $supportticket): void
    {
        //
    }
}
