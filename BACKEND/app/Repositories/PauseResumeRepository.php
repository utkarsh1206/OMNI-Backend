<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\LiveAgents;
use App\Models\PauseResumeUserLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PauseResumeRepository
{
    
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }
    public function logAction($userId, $campaignId, $extension, $action, $pauseCode = null)
    {
        try{
        $logData = [
            'user_id'     => $userId,
            'campaign_id' => $campaignId,
            'user_status' => $action,
        ];

        $logData['pause_code_id'] = ($action === 'Pause') ? $pauseCode : null;
        $logData['pause_time'] = ($action === 'Pause') ? Carbon::now()->toTimeString() : '00:00:00';
        $logData['resume_time'] = ($action === 'Resume') ? Carbon::now()->format('H:i:s') : '00:00:00';

        $logEntry = PauseResumeUserLog::create($logData);

        // Update agent status
        LiveAgents::where('user_id', $userId)->where('extension', $extension)->update(['status' => $action]);

        // Handle time calculations
        if ($action === 'Pause') {
            $this->updateTimeDifference($userId, $logEntry, 'Resume', 'total_resume_time');
            $this->handleDuplicateEntries($userId, 'Pause', 'total_resume_time');
        } else {
            $this->updateTimeDifference($userId, $logEntry, 'Pause', 'total_pause_time');
            $this->handleDuplicateEntries($userId, 'Resume', 'total_pause_time');
        }

        return $logEntry;
    } catch (ValidationException $e) {
        generateLogs('error', 'Error in Occupation List : ', $e->getMessage());
        return $this->apiResponse->handleAndResponseException($e);
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
            Log::warning("No previous $previousStatus log found for user ID: $userId.");
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
}
