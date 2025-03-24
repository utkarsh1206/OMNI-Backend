<?php

namespace App\Observers;

use App\Models\SupportTicketNotification;
use Illuminate\Support\Facades\Log;
use App\Services\SlaService;
use App\Models\Supportticket;

class SupportTicketNotificationObserver
{
    /**
     * Handle the SupportTicketNotification "created" event.
     */
    public function created(SupportTicketNotification $supportticketnotification): void
    {
        $support_ticket_id = $supportticketnotification->support_ticket_id;
        $supportticket = Supportticket::find($support_ticket_id);

        $sla_service = new SlaService();
        $openTicketData = $sla_service->calculateSla($support_ticket_id);

        Log::info("openTicketData " . json_encode($openTicketData));

        $updateData = [];

        if (isset($openTicketData['actual_resolved_time'])) {
            if ($supportticket->actual_resolved_time === null) {
                $updateData['actual_resolved_time'] = $openTicketData['actual_resolved_time'];
            }
        } else {
            $updateData['first_response_time'] = $openTicketData['first_response_time'];
            $updateData['alert_before_response_notification_time'] = $openTicketData['alert_before_response_notification_time'];
            $updateData['resolved_time'] = $openTicketData['resolved_time'];
            $updateData['alert_before_resolve_notification_time'] = $openTicketData['alert_before_resolve_notification_time'];
        }

        if ($supportticket->first_response_time != null && $supportticket->alert_before_response_notification_time != null && $supportticket->default_status != 'Open' && $supportticket->default_status != 'Resolved' && $supportticket->default_status != 'Closed') {
            $updateData['first_response_time'] =  $supportticket->first_response_time;
            $updateData['alert_before_response_notification_time'] = $supportticket->alert_before_response_notification_time;
            $updateData['resolved_time'] = null;
            $updateData['alert_before_resolve_notification_time'] = null;
        } elseif ($supportticket->first_response_time != null && $supportticket->alert_before_response_notification_time != null && $supportticket->default_status == 'Open') {
            $updateData['first_response_time'] =  $supportticket->first_response_time;
            $updateData['alert_before_response_notification_time'] = $supportticket->alert_before_response_notification_time;
            $updateData['resolved_time'] = $openTicketData['resolved_time'];
            $updateData['alert_before_resolve_notification_time'] = $openTicketData['alert_before_resolve_notification_time'];
        }
        // else if ($supportticket->default_status == 'Resolved' || $supportticket->default_status == 'Closed') {
        //     // $updateData['actual_resolved_time'] =  $openTicketData['actual_resolved_time'];
        // }

        // Update the support ticket with the available data
        if (!empty($updateData)) {
            //$supportticket->update($updateData);

            $ticketUpdated = Supportticket::where('id', $support_ticket_id)
                ->update($updateData);
        }
    }

    /**
     * Handle the Suppoirtticketnotification "updated" event.
     */
    public function updated(SupportTicketNotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the SupportTicketNotification "deleted" event.
     */
    public function deleted(SupportTicketNotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the SupportTicketNotification "restored" event.
     */
    public function restored(SupportTicketNotification $supportticketnotification): void
    {
        //
    }

    /**
     * Handle the SupportTicketNotification "force deleted" event.
     */
    public function forceDeleted(SupportTicketNotification $supportticketnotification): void
    {
        //
    }
}
