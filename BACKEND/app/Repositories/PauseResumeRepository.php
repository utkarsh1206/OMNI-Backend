<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\PauseResumeUserLog;
use App\Models\UserDailyActivity;
use Carbon\Carbon;
use Exception;

class PauseResumeRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function logAction($userId, $campaignId, $extension, $action, $pauseCode = null)
    {
        try {
            $logData = $this->prepareLogData($userId, $campaignId, $action, $pauseCode);
            $logEntry = PauseResumeUserLog::create($logData);

            // Update agent status
            $liveAgent = LiveAgents::where('user_id', $userId)
                ->where('extension', $extension)
                ->first();

            if ($liveAgent) {
                $liveAgent->status = $action;
                $liveAgent->save(); 
            }

            $this->handleActivityLogging($userId, $campaignId, $action, $pauseCode, $logEntry);

            return $logEntry;
        } catch (Exception $e) {
            generateLogs('error', 'Error in Pause/Resume API : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function prepareLogData($userId, $campaignId, $action, $pauseCode)
    {
        return [
            'user_id'       => $userId,
            'campaign_id'   => $campaignId,
            'user_status'   => $action,
            'pause_code_id' => $action === 'Pause' ? $pauseCode : null,
            'pause_time'    => $action === 'Pause' ? Carbon::now()->toTimeString() : '00:00:00',
            'resume_time'   => $action === 'Resume' ? Carbon::now()->toTimeString() : '00:00:00',
        ];
    }

    private function handleActivityLogging($userId, $campaignId, $action, $pauseCode, $logEntry)
    {
        if ($action === 'Pause') {
            $this->logUserDailyActivity($userId, $campaignId, 'Pause', 'Resume', $pauseCode);
            $this->updateTimeDifference($userId, $logEntry, 'Resume', 'total_resume_time');
            $this->handleDuplicateEntries($userId, 'Pause', 'total_resume_time');
        } else {
            $this->logUserDailyActivity($userId, $campaignId, 'Resume', 'Pause');
            $this->updateTimeDifference($userId, $logEntry, 'Pause', 'total_pause_time');
            $this->handleDuplicateEntries($userId, 'Resume', 'total_pause_time');
        }
    }

    private function updateTimeDifference($userId, $logEntry, $previousStatus, $field)
    {
        $lastLog = PauseResumeUserLog::where('user_status', $previousStatus)
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLog) {
            $timeDifference = Carbon::now()->timestamp - Carbon::parse($lastLog->created_at)->timestamp;
            $formattedTime = gmdate('H:i:s', max(0, $timeDifference));

            $logEntry->update([$field => $formattedTime]);
        } else {
            generateLogs('warning', "No previous $previousStatus log found for user ID: $userId.");
        }
    }

    private function handleDuplicateEntries($userId, $status, $field)
    {
        $logs = PauseResumeUserLog::where('user_id', $userId)
            ->where('user_status', $status)
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();

        if ($logs->count() > 1) {
            $logs[1]->update([$field => null]);
        }
    }

    protected function timeDifference($createdAt)
    {
        $start = Carbon::parse($createdAt);
        $end = Carbon::now();
        return $start->diff($end)->format('%H:%I:%S');
    }

    private function logUserDailyActivity($userId, $campaignId, $currentActivity, $previousActivity, $pauseCode = null)
    {
        $lastActivity = UserDailyActivity::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->where('activity', $previousActivity)
            ->orderByDesc('id')
            ->first();

        if ($lastActivity) {
            UserDailyActivity::create([
                'campaign_id'    => $campaignId,
                'user_id'        => $userId,
                'activity'       => $currentActivity,
                $currentActivity === 'Pause' ? 'resume_duration' : 'pause_duration' => $this->timeDifference($lastActivity->created_at),
                'pause_code'     => $currentActivity === 'Pause' ? $pauseCode : null,
            ]);
        }
    }
}
