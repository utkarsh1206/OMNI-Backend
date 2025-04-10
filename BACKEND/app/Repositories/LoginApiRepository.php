<?php

namespace App\Repositories;

use Carbon\Carbon;
use Exception;
use App\Http\Response\CustomApiResponse;

class LoginApiRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function loginData($connection, $username, $campaignId)
    {
        try {
            $user = $connection->table('users')
                ->select('id')
                ->where('username', $username)
                ->first();

            $userId = $user->id;
            $now = Carbon::now();

            // Begin transaction
            $connection->beginTransaction();

            // Log pause/resume activity
            $pauseResumeLog = [
                'user_id'     => $userId,
                'campaign_id' => $campaignId,
                'user_status' => 'Pause',
                'pause_time'  => $now->toTimeString(),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $connection->table('pause_resume_user_logs')->insert($pauseResumeLog);

            // Log daily user activity
            $userDailyActivities = [
                [
                    'user_id'     => $userId,
                    'campaign_id' => $campaignId,
                    'activity'    => 'Login',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [
                    'user_id'     => $userId,
                    'campaign_id' => $campaignId,
                    'activity'    => 'Pause',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]
            ];
            $connection->table('user_daily_activities')->insert($userDailyActivities);

            // Commit transaction
            $connection->commit();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                [
                    'pause_resume_log' => $pauseResumeLog,
                    'user_daily_activities' => $userDailyActivities
                ],
                trans('message.success')
            );
        } catch (Exception $ex) {
            // Rollback transaction on error
            $connection->rollBack();
            generateLogs('error', 'Error in logs login data: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
