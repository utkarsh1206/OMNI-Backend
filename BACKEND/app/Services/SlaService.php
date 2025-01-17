<?php

namespace App\Services;

use App\Models\SlaTarget;
use App\Models\SlaSetting;
use App\Models\SlaOffDay;
use App\Models\SlaHoliday;
use App\Models\IncidentType;
use App\Models\Supportticket;
use App\Models\Supportticketnotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SlaService
{
    public function calculateSla($support_ticket_id)
    {
        Log::info("SLA Calculation started for Ticket ID: {$support_ticket_id}");

        $ticket = Supportticket::where('default_status', 'Open')
            ->where('id', $support_ticket_id)
            ->where('is_delete', 'No')
            ->first();

        if ($ticket) {
            $incident = IncidentType::find($ticket->incident_id);
            $user = User::find($ticket->assign_to_id);
            $userLevel = $user->level;

            // Fetch SLA target
            $slaTarget = SlaTarget::where('sla_id', $incident->sla_id)
                ->where('priority_type', $ticket->default_priority)
                ->where('escalation_level', $userLevel)
                ->first();

            $offDays = SlaOffDay::where('sla_id', $incident->sla_id)->pluck('day')->toArray();
            $holidays = SlaHoliday::where('sla_id', $incident->sla_id)->pluck('holiday_date')->toArray();
            $slaSetting = SlaSetting::where('id', $incident->sla_id)->first();

            $openTicketData = $this->getOpenStatusData($ticket->id);
            Log::info("check count " . count($openTicketData));

            $openDate = $ticket->created_at;

            $totalSeconds = 0;
            foreach ($openTicketData as $openTicketItem) {
                if ($openTicketItem->last_subsequent_status_created_at) {
                    Log::info("Processing Ticket ID: " . $openTicketItem->support_ticket_id);

                    $startDate = $openTicketItem->open_status_created_at;
                    $endDate = $openTicketItem->last_subsequent_status_created_at;

                    $time = $this->calculateSlaTime($startDate, $endDate, $slaSetting, $offDays, $holidays);

                    list($hours, $minutes, $seconds) = explode(":", $time);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                    Log::info("SLA Calculation response: " . $totalSeconds);
                } else {
                    $openDate = $openTicketItem->open_status_created_at;
                    Log::info("Last open date $openDate ");
                }
            }

            if (!empty($slaTarget->resolve_time_value)) {
                $resolveTimeInSeconds = $slaTarget->resolve_time_value * 60;
                Log::info("resolveTimeInSeconds : {$resolveTimeInSeconds}, {$slaTarget->resolve_time_value}");

                if ($totalSeconds !== null) {
                    $resolveTimeData = $resolveTimeInSeconds - $totalSeconds;
                    Log::info("Adjusted Total Seconds after subtracting SLA resolve time: {$resolveTimeData}");
                } else {
                    $resolveTimeData = $resolveTimeInSeconds;
                    Log::info("Total Seconds set to SLA resolve time value: {$resolveTimeData}");
                }
            }

            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            Log::info("Calculated formatted Total Time: {$formattedTime}");

            // Adjust start time based on off days and holidays
            $startTime = $this->adjustStartTime($openDate, $incident->sla_id, $offDays, $holidays, $slaSetting);
            Log::info("Calculated SLA start time: {$startTime}");

            $firstResponseTime = null;
            $alertBeforeResponseNotificationTime = null;
            if (count($openTicketData) == 1) {
                // Calculate response times
                $firstResponseTime = $this->calculateFirstResponseTime($startTime, $slaTarget->response_time_value, $slaSetting, $ticket->created_at, $offDays, $holidays);
                Log::info("Calculated first response time: {$firstResponseTime}");

                $alertBeforeResponseNotificationTime = $firstResponseTime->copy()->subMinutes($slaTarget->alert_before_response_value);
                Log::info("Calculated alert before response notification time: {$alertBeforeResponseNotificationTime}");
            }

            // $resolvedTime = $this->calculateResolvedTime($startTime, $firstResponseTime, $slaTarget->resolve_time_value, $slaSetting, $ticket->created_at, $offDays, $holidays);
            $resolvedTime = $this->calculateResolvedTime($startTime, $firstResponseTime, $resolveTimeData, $slaSetting, $openDate, $offDays, $holidays);
            Log::info("Calculated resolved time: {$resolvedTime}");

            $alertBeforeResolveNotificationTime = $resolvedTime->copy()->subMinutes($slaTarget->alert_before_resolve_value);
            Log::info("Calculated alert before resolve notification time: {$alertBeforeResolveNotificationTime}");

            return [
                'first_response_time' => isset($firstResponseTime) ? $firstResponseTime->format('Y-m-d H:i:s') : null,
                'alert_before_response_notification_time' => isset($alertBeforeResponseNotificationTime) ? $alertBeforeResponseNotificationTime->format('Y-m-d H:i:s') : null,
                'resolved_time' => $resolvedTime ? $resolvedTime->format('Y-m-d H:i:s') : null,
                'alert_before_resolve_notification_time' => $alertBeforeResolveNotificationTime ? $alertBeforeResolveNotificationTime->format('Y-m-d H:i:s') : null,
            ];
        } else {
            return [
                'first_response_time' => null,
                'alert_before_response_notification_time' => null,
                'resolved_time' => null,
                'alert_before_resolve_notification_time' => null,
            ];
        }
    }

    private function adjustStartTime($createdAt, $slaId, $offDays, $holidays, $slaSetting)
    {
        // Convert created time to Carbon instance
        $startTime = Carbon::parse($createdAt);

        if ($slaSetting->is_24_hours == 1) {
            $businessStartTime = Carbon::parse('00:00:00');
            $businessEndTime = Carbon::parse('23:59:59');
        } else {
            $businessStartTime = Carbon::parse($slaSetting->start_time);
            $businessEndTime = Carbon::parse($slaSetting->end_time);
        }

        // Set the initial start time to the business start time
        if ($startTime->lt($businessStartTime)) {
            $startTime = $startTime->copy()->setTimeFromTimeString($slaSetting->start_time);
            Log::info("Created time is before business hours, setting start time to business start time: {$startTime}");
        } else if ($startTime->gt($businessEndTime)) {
            $startTime = $startTime->copy()->addDay()->setTimeFromTimeString($slaSetting->start_time);
            Log::info("Created time is after business hours, moving start time to next business day: {$startTime}");
        } else {
            Log::info("Created time is within business hours, keeping start time as: {$startTime}");
        }

        // Check for off days and holidays
        while ($this->isOffDayOrHoliday($startTime, $offDays, $holidays)) {
            $startTime->addDay()->setTimeFromTimeString($slaSetting->start_time);
            Log::info("Start time is an off day or holiday, moving to next business day: {$startTime}");
        }

        Log::info("Final adjusted start time: {$startTime}");
        return $startTime;
    }

    private function calculateFirstResponseTime($startTime, $responseTimeValue, $slaSetting, $createdAt, $offDays, $holidays)
    {
        $firstResponseTime = $startTime->copy()->addMinutes($responseTimeValue);
        $businessEndTime = Carbon::parse($slaSetting->end_time);
        $createdTime = Carbon::parse($createdAt);

        Log::info("start time for respose : {$startTime}");
        Log::info("firstResponseTime : {$firstResponseTime}");
        Log::info("createdTime : {$createdTime}");

        if ($this->isOffDayOrHoliday($createdTime, $offDays, $holidays) > 0) {
            return $firstResponseTime;
        }

        // Check if the first response time exceeds business hours
        if ($firstResponseTime->gt($businessEndTime)) {
            $remainingMinutes = $createdTime->diffInMinutes($businessEndTime);
            Log::info("remainingMinutes in first response : {$remainingMinutes}");

            if ($remainingMinutes < 0) {
                $remainingMinutes = 0;
            }
            // Adjust the response time value by subtracting the remaining minutes
            $adjustedResponseTimeValue = $responseTimeValue - $remainingMinutes;
            Log::info("adjustedResponseTimeValue in response time : {$adjustedResponseTimeValue}");

            // If the adjusted response time value is less than or equal to zero, set it to zero
            if ($adjustedResponseTimeValue < 0) {
                $adjustedResponseTimeValue = 0;
            }

            // Calculate the new first response time starting from the next business day
            $firstResponseTime = $businessEndTime->copy()->addDay()->setTimeFromTimeString($slaSetting->start_time)->addMinutes($adjustedResponseTimeValue);
        }

        while ($this->isOffDayOrHoliday($firstResponseTime, $offDays, $holidays)) {
            Log::info("First response time falls on an off day or holiday, moving to next business day: {$firstResponseTime}");
            $response = Carbon::parse($firstResponseTime);
            $firstResponseTime = $response->addDay(); // Skip the off day or holiday
        }

        return $firstResponseTime;
    }

    private function calculateResolvedTime($startTime, $firstResponseTime, $resolveTimeValue, $slaSetting, $createdAt, $offDays, $holidays)
    {
        // Convert resolveTimeValue (seconds) to minutes
        Log::info("resolveTimeValue time : {$resolveTimeValue}");
        $resolveTimeInMinutes = $resolveTimeValue / 60;

        // Calculate resolved time
        $createdTime = Carbon::parse($createdAt);
        $resolvedTime = $createdTime->copy()->addMinutes($resolveTimeInMinutes);
        $businessEndTime = Carbon::parse($slaSetting->end_time);

        Log::info("start time : {$startTime}");
        Log::info("resolvedTime time : {$resolvedTime}");
        Log::info("createdTime time : {$createdTime}");
        Log::info("businessEndTime time : {$businessEndTime}");

        if ($this->isOffDayOrHoliday($createdTime, $offDays, $holidays) > 0) {
            $resolvedTime = $startTime->copy()->setTimeFromTimeString($slaSetting->start_time)->addMinutes($resolveTimeInMinutes);
            Log::info("resolve time Start time is an off day or holiday, moving to next business day: {$resolvedTime}");

            return $resolvedTime;
        }

        if ($resolvedTime->gt($businessEndTime)) {
            // Calculate the remaining minutes from the created time to the business end time
            $remainingMinutes = $createdTime->diffInMinutes($businessEndTime);
            Log::info("remainingMinutes in resolve time : {$remainingMinutes}");

            if ($remainingMinutes < 0) {
                $remainingMinutes = 0;
            }
            // Adjust the resolve time value by subtracting the remaining minutes
            $adjustedResolveTimeValue = $resolveTimeInMinutes - $remainingMinutes;

            Log::info("adjustedResolveTimeValue in resolve time : {$adjustedResolveTimeValue}");
            // If the adjusted resolve time value is less than or equal to zero, set it to zero
            if ($adjustedResolveTimeValue < 0) {
                $adjustedResolveTimeValue = 0;
            }

            // Calculate the new resolved time starting from the next business day
            $resolvedTime = $businessEndTime->copy()->addDay()->setTimeFromTimeString($slaSetting->start_time)->addMinutes($adjustedResolveTimeValue);
        }

        while ($this->isOffDayOrHoliday($resolvedTime, $offDays, $holidays)) {
            $resolve = Carbon::parse($resolvedTime);
            $resolvedTime = $resolve->addDay(); // Skip the off day or holiday
            Log::info("First response time falls on an off day or holiday, moving to next business day: {$resolvedTime}");
        }

        Log::info("resolvedTime in resolve time : {$resolvedTime}");

        return $resolvedTime;
    }

    private function isOffDayOrHoliday(Carbon $date, array $offDays, array $holidays)
    {
        $dayOfWeek = $date->format('D');
        $isOffDay = in_array($dayOfWeek, $offDays);
        $holidayDate = $date->format('Y-m-d');
        $isHoliday = in_array($holidayDate, $holidays);

        Log::info("Off days : {$isOffDay}");
        return $isOffDay || $isHoliday;
    }

    public function getOpenStatusData($support_ticket_id)
    {
        $query = "WITH status_changes AS (
            SELECT
            id,
            status,
            support_ticket_id,
            created_at AS current_status_created_at,
            LEAD(status) OVER (PARTITION BY support_ticket_id ORDER BY created_at ASC) AS next_status,
            LEAD(created_at) OVER (PARTITION BY support_ticket_id ORDER BY created_at ASC) AS next_status_created_at FROM `support_ticket_notification`
        )
        SELECT
            support_ticket_id,
            status AS open_status,
            current_status_created_at AS open_status_created_at,
            next_status AS last_subsequent_status,
            next_status_created_at AS last_subsequent_status_created_at
            FROM status_changes
            WHERE status = 'Open' AND support_ticket_id = $support_ticket_id";

        // Execute the query using DB::select() with the query string.
        $results = DB::select($query);
        return $results;
    }

    private function calculateSlaTime($start_date, $end_date, $slaSetting, $offDays, $holidays)
    {
        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);

        $businessHoursStart = $slaSetting->start_time;
        $businessHoursEnd = $slaSetting->end_time;

        // Calculate daily business minutes dynamically
        $startOfBusinessDay = Carbon::createFromTimeString($slaSetting->start_time);
        $endOfBusinessDay = Carbon::createFromTimeString($slaSetting->end_time);
        $dailyBusinessMinutes = $startOfBusinessDay->diffInMinutes($endOfBusinessDay);

        $totalBusinessMinutes = 0;
        $totalBusinessSeconds = 0;

        // Check if the start and end dates are within business hours on the same day
        if ($startDate->isSameDay($endDate)) {
            if (!$this->isOffDayOrHoliday($startDate, $offDays, $holidays)) {
                $effectiveStart = max($startDate, $startOfBusinessDay->copy()->setDateFrom($startDate));
                $effectiveEnd = min($endDate, $endOfBusinessDay->copy()->setDateFrom($startDate));

                if ($effectiveStart->lt($effectiveEnd)) {
                    $totalBusinessSeconds += $effectiveStart->diffInSeconds($effectiveEnd);
                    Log::info("Effective Start: {$effectiveStart}, Effective End: {$effectiveEnd}, Seconds: {$totalBusinessSeconds}");
                }
            }

            return $this->formatSecondsToTime($totalBusinessSeconds);
        }

        // Handle multi-day logic
        $current = $startDate->copy();

        if (!$this->isOffDayOrHoliday($current, $offDays, $holidays)) {
            $effectiveStart = max($current, $startOfBusinessDay->copy()->setDateFrom($current));
            $effectiveEnd = $endOfBusinessDay->copy()->setDateFrom($current);

            if ($effectiveStart->lt($effectiveEnd)) {
                $totalBusinessSeconds += $effectiveStart->diffInSeconds($effectiveEnd);
            }
        }

        // Calculate full business hours for the days between start and end dates
        $current->addDay();
        while ($current->lt($endDate->copy()->startOfDay())) {
            if (!$this->isOffDayOrHoliday($current, $offDays, $holidays)) {
                $totalBusinessSeconds += $dailyBusinessMinutes * 60;
            }
            $current->addDay();
        }

        if (!$this->isOffDayOrHoliday($endDate, $offDays, $holidays)) {
            $effectiveStart = $startOfBusinessDay->copy()->setDateFrom($endDate);
            $effectiveEnd = min($endDate, $endOfBusinessDay->copy()->setDateFrom($endDate));

            if ($effectiveStart->lt($effectiveEnd)) {
                $totalBusinessSeconds += $effectiveStart->diffInSeconds($effectiveEnd);
            }
        }

        return $this->formatSecondsToTime($totalBusinessSeconds);
    }

    private function formatSecondsToTime($totalSeconds)
    {
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        Log::info("hours {$hours}, minutes {$minutes}, seconds {$seconds}");
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
