<?php

namespace App\Services;

use App\Models\SlaTarget;
use App\Models\SlaSetting;
use App\Models\SlaOffDay;
use App\Models\SlaHoliday;
use App\Models\IncidentType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlaService
{
    public function calculateSla($ticket)
    {
        Log::info("SLA Calculation started for Ticket ID: {$ticket->id}");

        // Fetch incident data and user level
        $ticket->incident_id = 2;
        $incident = IncidentType::findOrFail($ticket->incident_id);

        $user = User::find($ticket->assign_to_id);
        $userLevel = $user->level;
        Log::info("Assigned user ID: {$ticket->assign_to_id}, User Level: {$userLevel}");

        // Fetch SLA target
        $slaTarget = SlaTarget::where('sla_id', $incident->sla_id)
            ->where('priority_type', $ticket->default_priority)
            ->where('escalation_level', $userLevel)
            ->first();

        // Fetch off days and holidays
        $offDays = SlaOffDay::where('sla_id', $incident->sla_id)->pluck('day')->toArray();
        Log::info("Fetched off days for SLA ID {$incident->sla_id}: ", $offDays);

        $holidays = SlaHoliday::where('sla_id', $incident->sla_id)->pluck('holiday_date')->toArray();
        Log::info("Fetched holidays: ", $holidays);

        // Fetch SLA settings
        $slaSetting = SlaSetting::where('id', $incident->sla_id)->first();

        // Test case date (Can be dynamic from the ticket)
        // $ticket->created_at = "2025-01-03 21:48:31";

        // Adjust start time based on off days and holidays
        $startTime = $this->adjustStartTime($ticket->created_at, $incident->sla_id, $offDays, $holidays, $slaSetting);
        Log::info("Calculated SLA start time: {$startTime}");

        // Calculate response times
        $firstResponseTime = $startTime->copy()->addMinutes($slaTarget->response_time_value);
        Log::info("Calculated first response time: {$firstResponseTime}");

        $alertBeforeResponseNotificationTime = $firstResponseTime->copy()->subMinutes($slaTarget->alert_before_response_value);
        Log::info("Calculated alert before response notification time: {$alertBeforeResponseNotificationTime}");

        // Calculate resolved time
        $resolvedTime = $startTime->copy()->addMinutes($slaTarget->resolve_time_value);

        // Adjust resolved time if it exceeds the end time
        if ($resolvedTime->gt($slaSetting->end_time)) {
            $resolvedTime = $resolvedTime->copy()->addDay()->setTimeFromTimeString($slaSetting->start_time);
            $resolvedTime = $resolvedTime->addMinutes($slaTarget->resolve_time_value); // Add resolve time on the next business day
        }

        Log::info("Calculated resolved time: {$resolvedTime}");

        $alertBeforeResolveNotificationTime = $resolvedTime->copy()->subMinutes($slaTarget->alert_before_resolve_value);
        Log::info("Calculated alert before resolve notification time: {$alertBeforeResolveNotificationTime}");

        return [
            'first_response_time' => $firstResponseTime,
            'alert_before_response_notification_time' => $alertBeforeResponseNotificationTime,
            'resolved_time' => $resolvedTime,
            'alert_before_resolve_notification_time' => $alertBeforeResolveNotificationTime,
        ];
    }

    private function adjustStartTime($createdAt, $slaId, $offDays, $holidays, $slaSetting)
    {
        Log::info("Adjusting start time for created_at: {$createdAt}");

        // Convert created time to Carbon instance
        $startTime = Carbon::parse($createdAt);

        if ($slaSetting->is_24_hours == 1) {
            // If 24 hours, no need to adjust based on business start and end time
            Log::info("SLA setting is 24 hours, no adjustment needed for business hours.");
            return $startTime;
        }

        $businessStartTime = Carbon::parse($slaSetting->start_time);
        $businessEndTime = Carbon::parse($slaSetting->end_time);

        Log::info("business start time: {$businessStartTime}");
        Log::info("business end time: {$businessEndTime}");

        // Set the initial start time to the business start time if it's before business hours
        if ($startTime->lt($businessStartTime)) {
            $startTime = $startTime->copy()->setTimeFromTimeString($slaSetting->start_time);
            Log::info("Created time is before business hours, setting start time to business start time: {$startTime}");
        } else {
            // If created time is after business hours, set start time to the next business day
            if ($startTime->gt($businessEndTime)) {
                $startTime = $startTime->copy()->addDay()->setTimeFromTimeString($slaSetting->start_time);
                Log::info("Created time is after business hours. Setting start time to next business day: {$startTime}");
            }
        }

        Log::info("Initial start time: {$startTime}");

        // Check if tomorrow is Saturday or Sunday using the `isOffDayOrHoliday` method
        $nextDay = $startTime->copy()->addDay();  // Get the next day
        Log::info("Checking if next day {$nextDay->format('Y-m-d')} is an off day (Saturday/Sunday)");

        if ($this->isOffDayOrHoliday($nextDay, $offDays, $holidays)) {
            Log::info("Next day is an off day (Saturday/Sunday), skipping to next business day...");
            $nextDay->addDay(); // Skip to the next day
        }

        // Final adjusted start time
        Log::info("Final adjusted start time: {$nextDay->format('Y-m-d H:i:s')}");
        return $nextDay;
    }

    private function isOffDayOrHoliday(Carbon $date, array $offDays, array $holidays)
    {
        // Get the abbreviated weekday (e.g., "Sat" for Saturday, "Sun" for Sunday)
        $dayOfWeek = $date->format('D');  // Will return "Mon", "Tue", ..., "Sun"
        Log::info("Checking if {$dayOfWeek} is an off day or holiday...");

        // Check if the day is an off day (e.g., Sat, Sun)
        $isOffDay = in_array($dayOfWeek, $offDays);
        Log::info("isOffDay: {$isOffDay} (Checking: {$dayOfWeek} against offDays: " . json_encode($offDays) . ")");

        // Get the full date format (e.g., "2025-01-01")
        $holidayDate = $date->format('Y-m-d'); // Get "2025-01-03" format
        Log::info("Checking if {$holidayDate} is a holiday...");

        // Check if the day is a holiday (e.g., 2025-01-01)
        $isHoliday = in_array($holidayDate, $holidays);
        Log::info("isHoliday: {$isHoliday} (Checking: {$holidayDate} against holidays: " . json_encode($holidays) . ")");

        // Return true if the date is an off day or a holiday
        return $isOffDay || $isHoliday;
    }
}
