<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\NeoxCallLog;
use App\Models\PauseResumeUserLog;
use App\Models\UserDailyActivity;
use App\Models\UserLoginLog;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class UserActivityReportRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    /**
     * Get the user login report.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReport(Request $request)
    {
        try {
            $perPage = $request->input('perPage', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            // Validate required datetime filters
            if (!$request->has(['startDate', 'endDate'])) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            // Parse start and end datetimes
            $startDatetime = Carbon::parse($request->startDate);
            $endDatetime = Carbon::parse($request->endDate);

            // Get the authenticated user
            $user = auth()->user();

            // Build the base query
            $query = UserLoginLog::select([
                'user_id',
                'login_date',
                DB::raw("SUM(TIMESTAMPDIFF(SECOND, CONCAT(login_date, ' ', login_time), CONCAT(logout_date, ' ', logout_time))) as total_seconds"),
                DB::raw("COUNT(*) as login_count")
            ])
                ->whereNotNull('logout_time')
                ->whereBetween('login_date', [$startDatetime, $endDatetime])
                ->groupBy('login_date', 'user_id')
                ->with('user')
                ->orderByDesc('login_date');

            // Apply user_id filter if role_id is 3
            if ($user->role_id == 3) {
                $query->where('user_id', $user->id);
            }

            // Handle different response formats
            if ($page == config('omni.ALL_PAGE')) {
                $transformedData = $query->get()
                    ->groupBy('login_date')
                    ->map(function ($group) {
                        return $group->map(function ($item) {
                            return [
                                'user_id' => $item->user_id,
                                'user_name' => $item->user->username ?? null,
                                'total_duration' => gmdate('H:i:s', $item->total_seconds),
                                'login_count' => $item->login_count
                            ];
                        });
                    });
            } else {
                // For paginated response
                $paginatedData = $query->paginate($perPage);

                // Transform the items while maintaining pagination structure
                $transformedData = $paginatedData->setCollection(
                    $paginatedData->getCollection()
                        ->groupBy('login_date')
                        ->map(function ($group) {
                            return $group->map(function ($item) {
                                return [
                                    'user_id' => $item->user_id,
                                    'user_name' => $item->user->username ?? null,
                                    'total_duration' => gmdate('H:i:s', $item->total_seconds),
                                    'login_count' => $item->login_count
                                ];
                            });
                        })
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $transformedData,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getReport: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Get the activity report data for the user
     *
     * @param Request $request
     * @return array
     */
    public function getData(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $date = $request->input('date');

            // Fetch necessary data
            $log_data = $this->getLoginLogs($user_id, $date);
            $pause_resume_data = $this->getPauseResumeLogs($user_id, $date);
            $pause_resume_total = $this->getTotalPauseResumeTime($user_id, $date);
            $user_name = $this->getUserNameById($user_id);
            $i_call_data = $this->getCallData($user_name, $date, 'incoming');
            $o_call_data = $this->getCallData($user_name, $date, 'outgoing');
            $incoming = $this->getCallHistoryData('incoming', $user_name, $date);
            $outgoing = $this->getCallHistoryData('outgoing', $user_name, $date);
            $ready_data = $this->getReadyData($user_id, $date);
            $dash_data = $this->getReportData($user_id, '', $date);
            $todaysLoginData = $this->getTodaysLoginTime($request);
            $pause_resume_activity = $this->getActivityPauseData($user_id, $date);
            $daily_user_activity = $this->getDailyUserActivity($user_id, $date);
            $total_duration = $this->getTotalDuration($user_id, $date);

            $data = [
                'log_data' => $log_data,
                'pause_resume_data' => $pause_resume_data,
                'i_call_data' => $i_call_data,
                'o_call_data' => $o_call_data,
                'incoming' => $incoming,
                'outgoing' => $outgoing,
                'dash_data' => $dash_data,
                'ready_data' => $ready_data,
                'pause_resume_activity' => $pause_resume_activity,
                'date' => $date,
                'pause_resume_total' => $pause_resume_total,
                'daily_user_activity' => $daily_user_activity,
                'total_duration' => $total_duration,
                'todays_login_time' => $todaysLoginData['todays_login_time'],
                'total_login_time' => $todaysLoginData['total_login_time'],
            ];

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getData: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Get login logs for the user
     */
    private function getLoginLogs($user_id, $date)
    {
        try {
            return UserLoginLog::select('id', 'login_date', 'logout_date', 'login_time', 'logout_time')
                ->where('user_id', $user_id)
                ->where('login_date', $date)
                ->get();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getLoginLogs: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get pause/resume logs for the user
     */
    private function getPauseResumeLogs($user_id, $date)
    {
        try {
            return PauseResumeUserLog::select('id', 'pause_time', 'resume_time', 'user_status', 'pause_code_id')
                ->where('user_id', $user_id)
                ->whereDate('created_at', $date)
                ->get();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getPauseResumeLogs: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the total pause and resume times
     */
    private function getTotalPauseResumeTime($user_id, $date)
    {
        try {
            return PauseResumeUserLog::selectRaw("SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))) AS total_resume, SEC_TO_TIME(SUM(TIME_TO_SEC(total_pause_time))) AS total_pause")
                ->where('user_id', $user_id)
                ->whereDate('created_at', $date)
                ->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getTotalPauseResumeTime: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the username of the user
     */
    private function getUserNameById($user_id)
    {
        try {
            return Users::where('id', $user_id)->pluck('username')->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getUserNameById: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get incoming or outgoing call data for the user
     */
    private function getCallData($user_name, $date, $call_type)
    {
        try {
            return DB::table('neox_call_logs as nl')
                ->select(DB::raw('count(id) as total'))
                ->where('caller_username', $user_name)
                ->whereDate('created_at', $date)
                ->where('call_type', $call_type)
                ->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getCallData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get call history data for incoming or outgoing calls
     */
    private function getCallHistoryData($call_type, $user_name, $date)
    {
        try {
            return NeoxCallLog::select(
                'caller_user_extension',
                'called_phone_number',
                DB::raw('SEC_TO_TIME(call_talk_duration) as call_talk_duration'),
                'call_dispo_status',
                'created_at',
                'call_date'
            )
                ->where('call_type', $call_type)
                ->where('caller_username', $user_name)
                ->whereDate('call_date', $date)
                ->where('type', 'call_history')
                ->get();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getCallHistoryData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get ready data (pause/resume data)
     */
    private function getReadyData($user_id, $date)
    {
        try {
            return PauseResumeUserLog::selectRaw(
                "TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(total_resume_time))), '%H:%i:%s') AS ready_avg, 
                 TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))), '%H:%i:%s') AS ready"
            )
                ->where('user_id', $user_id)
                ->whereDate('created_at', $date)
                ->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getReadyData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get today's login time
     */
    public function getTodaysLoginTime(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $todayDate = Carbon::today()->toDateString();
            $totalLoginTime = '';
            $todaysLoginTime = false;

            // Get the latest login record for today
            $loginDetail = UserLoginLog::where('user_id', $userId)
                ->whereDate('login_date', $todayDate)
                ->orderBy('id', 'desc')
                ->first();

            if ($request->input('date') == $todayDate && $loginDetail) {
                if ($loginDetail->logout_time == '00:00:00' && $loginDetail->logout_date == '0000-00-00') {
                    // Get the first login time for today
                    $firstLoginTime = UserLoginLog::where('user_id', $userId)
                        ->whereDate('login_date', $todayDate)
                        ->orderBy('id', 'asc')
                        ->value('login_time');

                    // Calculate the difference in time between the first login time and now
                    $totalLoginTime = $this->timeDifferenceInHMS($firstLoginTime, Carbon::now()->format('H:i:s'));
                    $todaysLoginTime = true;
                }
            }

            return [
                'todays_login_time' => $todaysLoginTime,
                'total_login_time' => $totalLoginTime
            ];
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getTodaysLoginTime: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate the difference in time in H:M:S format
     */
    private function timeDifferenceInHMS($startTime, $endTime)
    {
        $start = Carbon::createFromFormat('H:i:s', $startTime);
        $end = Carbon::createFromFormat('H:i:s', $endTime);

        $diff = $start->diff($end);

        return $diff->format('%H:%I:%S');
    }

    /**
     * Get daily user activity
     */
    private function getDailyUserActivity($user_id, $date)
    {
        try {
            return UserDailyActivity::with('pauseCode')
                ->where('user_id', $user_id)
                ->when($date, function ($query) use ($date) {
                    $query->whereDate('created_at', date('Y-m-d', strtotime($date)));
                })
                ->get()
                ->map(function ($activity) {
                    $activityArray = $activity->toArray();
                    $activityArray['pause_code'] = optional($activity->pauseCode)->code ?? null;
                    return $activityArray;
                });
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getDailyUserActivity: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total duration of various activities for the user
     */
    private function getTotalDuration($user_id, $date)
    {
        try {
            return UserDailyActivity::selectRaw("
            SEC_TO_TIME(SUM(TIME_TO_SEC(total_login_duration))) AS total_login_d,
            SEC_TO_TIME(SUM(TIME_TO_SEC(pause_duration))) AS total_pause_d,
            SEC_TO_TIME(SUM(TIME_TO_SEC(resume_duration))) AS total_resume_d,
            SEC_TO_TIME(SUM(TIME_TO_SEC(call_duration))) AS total_call_d,
            SEC_TO_TIME(SUM(TIME_TO_SEC(dispo_duration))) AS total_dispose_d
        ")
                ->where('user_id', $user_id)
                ->when($date, function ($query) use ($date) {
                    $query->whereDate('created_at', date('Y-m-d', strtotime($date)));
                })
                ->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getTotalDuration: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the report data
     */
    public function getReportData($userId, $campaignId = '', $date)
    {
        try {
            $subConditions = [];
            $loginConditions = [];

            if ($date) {
                $dateCondition = "DATE(created_at) = '{$date}'";
                $subConditions[] = $dateCondition;
                $loginConditions[] = "login_date = '{$date}'";
            }

            $subConditions[] = "u.id = {$userId}";

            if (!empty($campaignId)) {
                $subConditions[] = "campaign_id = '{$campaignId}'";
                $loginConditions[] = "campaign_id = '{$campaignId}'";
            }

            $subCondStr = ' AND ' . implode(' AND ', $subConditions);
            $loginCondStr = !empty($loginConditions) ? ' AND ' . implode(' AND ', $loginConditions) : '';

            $result = DB::table('users as u')
                ->leftJoin('user_login_logs as ul', function ($join) use ($loginCondStr) {
                    $join->on('ul.user_id', '=', 'u.id');
                    if (!empty($loginCondStr)) {
                        $join->whereRaw(ltrim($loginCondStr, ' AND '));
                    }
                })
                ->selectRaw("
                (SELECT COUNT(*) FROM neox_call_logs nl WHERE nl.type = 'call_history' AND nl.caller_username = u.username {$subCondStr}) as calls,
                (SELECT COUNT(*) FROM neox_call_logs nl WHERE nl.call_type = 'outgoing' AND nl.type = 'call_history' AND nl.caller_username = u.username {$subCondStr}) as outgoing_call,
                (SELECT COUNT(*) FROM neox_call_logs nl WHERE nl.call_type = 'incoming' AND nl.type = 'call_history' AND nl.caller_username = u.username {$subCondStr}) as incoming_call,
                (SELECT COUNT(*) FROM neox_call_logs nl WHERE nl.type = 'CALL_CONNECTED' AND nl.caller_username = u.username {$subCondStr}) as answered,
                IFNULL((SELECT SEC_TO_TIME(ROUND(SUM(call_talk_duration))) FROM neox_call_logs nl WHERE nl.type = 'call_history' AND nl.caller_username = u.username {$subCondStr}), '00:00:00') as total_talk,
                IFNULL((SELECT SEC_TO_TIME(ROUND(AVG(call_talk_duration))) FROM neox_call_logs nl WHERE nl.type = 'call_history' AND nl.caller_username = u.username {$subCondStr}), '00:00:00') as talk_avg,
                COUNT(ul.id) as login_count,
                IF(MIN(ul.login_date) IS NOT NULL, CONCAT(MIN(ul.login_date), ' ', MIN(ul.login_time)), 'Not Logged') as first_login,
                IF(MAX(ul.login_date) IS NOT NULL, CONCAT(MAX(ul.login_date), ' ', MAX(ul.login_time)), 'Not Logged') as last_log,
                IF(MAX(ul.logout_date) IS NOT NULL, CONCAT(MAX(ul.logout_date), ' ', MAX(ul.logout_time)), 'Not Logged') as last_logout,
                IFNULL((
                    SELECT SEC_TO_TIME(SUM(
                        TIMESTAMPDIFF(
                            SECOND,
                            STR_TO_DATE(CONCAT(login_date, ' ', login_time), '%Y-%m-%d %H:%i:%s'),
                            STR_TO_DATE(CONCAT(logout_date, ' ', logout_time), '%Y-%m-%d %H:%i:%s')
                        )
                    ))
                    FROM user_login_logs
                    WHERE user_id = u.id
                    AND logout_time != '00:00:00'
                    " . ($date ? "AND login_date = '{$date}'" : "") . "
                ), 0) as total_log_time,
                IFNULL((SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(total_pause_time))) 
                        FROM pause_resume_user_logs 
                        WHERE user_id = u.id 
                        " . ($date ? "AND DATE(created_at) = '{$date}'" : "") . " 
                        GROUP BY user_id), '00:00:00') as pause_time,
                IFNULL((SELECT SEC_TO_TIME(ROUND(AVG(TIME_TO_SEC(total_pause_time)))) 
                        FROM pause_resume_user_logs 
                        WHERE user_id = u.id 
                        " . ($date ? "AND DATE(created_at) = '{$date}'" : "") . " 
                        GROUP BY user_id), '00:00:00') as pause_time_avg,
                IFNULL((SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))) 
                        FROM pause_resume_user_logs 
                        WHERE user_id = u.id 
                        " . ($date ? "AND DATE(created_at) = '{$date}'" : "") . " 
                        GROUP BY user_id), '00:00:00') as resume_time,
                IFNULL((SELECT SEC_TO_TIME(ROUND(AVG(TIME_TO_SEC(total_resume_time)))) 
                        FROM pause_resume_user_logs 
                        WHERE user_id = u.id 
                        " . ($date ? "AND DATE(created_at) = '{$date}'" : "") . " 
                        GROUP BY user_id), '00:00:00') as resume_time_avg
                ")
                ->where('u.id', $userId)
                ->groupBy('u.id', 'u.username')
                ->orderByDesc('u.id')
                ->first();

            if ($result) {
                foreach (['first_login', 'last_log', 'last_logout'] as $field) {
                    $timestamp = $result->$field;
                    if (!$timestamp || strtotime($timestamp) < strtotime('1972-01-01')) {
                        $result->$field = 'Not Logged';
                    } else {
                        $result->$field = Carbon::parse($timestamp)->format('Y-m-d H:i:s');
                    }
                }
            }

            return $result;
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getReportData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get pause and resume activity data
     */
    public function getActivityPauseData($user_id, $date)
    {
        try {
            return PauseResumeUserLog::select(
                'id',
                'pause_time',
                'resume_time',
                'user_status',
                'pause_code_id',
                DB::raw('IF(user_status = "Pause", pause_time, resume_time) AS time'),
                'total_resume_time',
                'total_pause_time'
            )
                ->where('user_id', $user_id)
                ->whereDate('created_at', $date)
                ->get();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getActivityPauseData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export user activity report to CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportReportToCsv(Request $request)
    {
        try {
            // Validate required datetime filters
            if (!$request->has(['startDate', 'endDate'])) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            // Get the main report data (same as getReport method)
            $startDatetime = Carbon::parse($request->startDate);
            $endDatetime = Carbon::parse($request->endDate);

            // Get the authenticated user
            $user = auth()->user();

            $query = UserLoginLog::with('user')
                ->whereBetween('created_at', [$startDatetime, $endDatetime]);

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Apply user_id filter if role_id is 3
            if ($user->role_id == 3) {
                $query->where('user_id', $user->id);
            }

            $logs = $query->get();

            if ($logs->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            $groupedData = $logs->groupBy(['login_date', 'user_id']);

            // Prepare headers for the CSV
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="user_activity_report_' . date('Y-m-d') . '.csv"',
            ];

            // Create a callback function for the StreamedResponse
            $callback = function () use ($groupedData, $request) {
                $file = fopen('php://output', 'w');

                // Main report headers
                fputcsv($file, [
                    'Date',
                    'Username',
                    'Total Duration',
                    'Login Count'
                ]);

                // Process each main report row
                foreach ($groupedData as $date => $users) {
                    foreach ($users as $userId => $entries) {
                        $totalDuration = 0;

                        foreach ($entries as $log) {
                            if ($log->login_time && $log->logout_time) {
                                $loginTime = Carbon::parse("{$log->login_date} {$log->login_time}");
                                $logoutTime = Carbon::parse("{$log->logout_date} {$log->logout_time}");
                                $totalDuration += $loginTime->diffInSeconds($logoutTime);
                            }
                        }

                        // Write main report row
                        fputcsv($file, [
                            $date,
                            optional($entries->first()->user)->username,
                            gmdate('H:i:s', $totalDuration),
                            $entries->count()
                        ]);

                        // Get detailed activity data for this user/date
                        $detailedRequest = new Request([
                            'user_id' => $userId,
                            'date' => $date
                        ]);

                        try {
                            $activityData = $this->getData($detailedRequest);
                            $responseContent = json_decode($activityData->getContent(), true); // associative array

                            if (isset($responseContent['data'])) {
                                $details = json_decode(json_encode($responseContent['data'])); // Convert to object
                            } else {
                                $details = null;
                            }

                            if ($details) {
                                // $details = json_decode(json_encode($activityData->getData()->data));

                                // Add separator
                                fputcsv($file, ['--- DETAILED ACTIVITY DATA ---']);

                                fputcsv($file, ['First Login', 'Last Logout', 'Total Log Time']);
                                fputcsv($file, [
                                    $details->dash_data->first_login ?? '00:00:00',
                                    $details->dash_data->last_logout ?? '00:00:00',
                                    $details->dash_data->total_log_time ?? '00:00:00'
                                ]);

                                fputcsv($file, ['']);
                                fputcsv($file, ['Total Talk', 'Talk Average', 'Total Pause Time', 'Pause Average', 'Total Ready Time', 'Ready Average']);
                                fputcsv($file, [
                                    $details->dash_data->total_talk ?? '00:00:00',
                                    $details->dash_data->talk_avg ?? '00:00:00',
                                    $details->dash_data->pause_time ?? '00:00:00',
                                    $details->dash_data->pause_time_avg ?? '00:00:00',
                                    $details->ready_data->ready ?? '00:00:00',
                                    $details->ready_data->ready_avg ?? '00:00:00'
                                ]);

                                fputcsv($file, ['']);
                                fputcsv($file, ['--- INCOMING DATA ---']);
                                fputcsv($file, ['Caller Number', 'Call Time', 'Call Duration', 'Dispo Stat']);
                                if (!empty($details->incoming) && count($details->incoming) > 0) {
                                    foreach ($details->incoming as $incomingData) {
                                        fputcsv($file, [
                                            $incomingData->called_phone_number,
                                            $incomingData->call_date,
                                            $incomingData->call_talk_duration,
                                            $incomingData->call_dispo_status
                                        ]);
                                    }
                                } else {
                                    fputcsv($file, ['No Data']);
                                }

                                fputcsv($file, ['']);
                                fputcsv($file, ['--- OUTGOING DATA ---']);
                                fputcsv($file, ['Caller Number', 'Call Time', 'Call Duration', 'Dispo Stat']);
                                if (!empty($details->outgoing) && count($details->outgoing) > 0) {
                                    foreach ($details->outgoing as $outgoingData) {
                                        fputcsv($file, [
                                            $outgoingData->called_phone_number,
                                            $outgoingData->call_date,
                                            $outgoingData->call_talk_duration,
                                            $outgoingData->call_dispo_status
                                        ]);
                                    }
                                } else {
                                    fputcsv($file, ['No Data']);
                                }

                                fputcsv($file, ['']);
                                fputcsv($file, ['--- DAILY USER ACTIVITY ---']);
                                fputcsv($file, ['Date/Time', 'Activity', 'Total Login Duration', 'Pause Duration', 'Pause Code', 'Resume Duration', 'Call Duration', 'Dispose Duration', 'Dispose Code']);
                                if (!empty($details->daily_user_activity) && count($details->daily_user_activity) > 0) {
                                    foreach ($details->daily_user_activity as $dailyUserActivity) {
                                        fputcsv($file, [
                                            $dailyUserActivity->created_at,
                                            $dailyUserActivity->activity,
                                            $dailyUserActivity->total_login_duration,
                                            $dailyUserActivity->pause_duration,
                                            $dailyUserActivity->pause_code,
                                            $dailyUserActivity->resume_duration,
                                            $dailyUserActivity->call_duration,
                                            $dailyUserActivity->dispo_duration,
                                            $dailyUserActivity->dispo_code
                                        ]);
                                    }
                                } else {
                                    fputcsv($file, ['No Data']);
                                }
                                fputcsv($file, ['']);

                                fputcsv($file, ['--- TOTAL DURATIONS ---']);
                                if (!empty($details->total_duration)) {
                                    fputcsv($file, [
                                        'Total Login Duration',
                                        'Total Pause Duration',
                                        'Total Resume Duration',
                                        'Total Call Duration',
                                        'Total Disposition Duration'
                                    ]);
                                    $total = $details->total_duration;
                                    fputcsv($file, [
                                        $total->total_login_d ?? '00:00:00',
                                        $total->total_pause_d ?? '00:00:00',
                                        $total->total_resume_d ?? '00:00:00',
                                        $total->total_call_d ?? '00:00:00',
                                        $total->total_dispose_d ?? '00:00:00'
                                    ]);
                                } else {
                                    fputcsv($file, ['No daily activity data found']);
                                }
                                fputcsv($file, ['']);

                                fputcsv($file, ['--- LOGIN/LOGOUT TIME ---']);
                                fputcsv($file, ['Login Time', 'Login Time']);
                                if (!empty($details->log_data) && count($details->log_data) > 0) {
                                    foreach ($details->log_data as $logData) {
                                        fputcsv($file, [
                                            $logData->login_time = (!is_null($logData->login_time))
                                                ? $logData->login_time : 'Closed Browser',
                                            $logData->logout_time = (!is_null($logData->logout_time))
                                                ? $logData->logout_time : 'Closed Browser'
                                        ]);
                                    }
                                }
                                fputcsv($file, ['']);

                                fputcsv($file, ['--- PAUSE/RESUME ACTIVITY ---']);
                                fputcsv($file, ['Activity', 'Time', 'Reason']);
                                if (!empty($details->pause_resume_activity) && count($details->pause_resume_activity) > 0) {
                                    foreach ($details->pause_resume_activity as $pauseResumeActivity) {
                                        fputcsv($file, [
                                            $pauseResumeActivity->user_status,
                                            $pauseResumeActivity->time,
                                            $pauseResumeActivity->pause_code_id ?? ''
                                        ]);
                                    }
                                } else {
                                    fputcsv($file, ['No Data']);
                                }
                                fputcsv($file, ['']);


                                fputcsv($file, ['--- TOTAL INCOMING/OUTGOING ---']);
                                fputcsv($file, ['Total Incoming', 'Total Outgoing']);
                                fputcsv($file, [
                                    $details->i_call_data->total,
                                    $details->o_call_data->total,
                                ]);

                                // Add separator before next main row
                                fputcsv($file, ['']);
                                fputcsv($file, ['--- END OF DETAILED DATA ---']);
                                fputcsv($file, ['']);
                            }
                        } catch (Exception $e) {
                            // Log error but continue with next record
                            generateLogs('error', "Error fetching detailed data for user $userId on $date: " . $e->getMessage());
                            fputcsv($file, ['Error loading detailed activity data']);
                            fputcsv($file, ['']);
                        }
                    }
                }

                fclose($file);
            };

            return new StreamedResponse($callback, ResponseHTTP::HTTP_OK, $headers);
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@exportReportToCsv: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getGraphsRecord(Request $request)
    {
        try {
            $start_date = Carbon::parse($request->input('startDate'));
            $end_date = Carbon::parse($request->input('endDate'));

            // Get authenticated user
            $user = auth()->user();

            // Pause Count Query
            $pauseQuery = PauseResumeUserLog::selectRaw('count(id) as total')
                ->where('user_status', 'Pause')
                ->whereBetween(DB::raw('DATE(created_at)'), [$start_date->toDateString(), $end_date->toDateString()]);

            // Resume Count Query
            $resumeQuery = PauseResumeUserLog::selectRaw('count(id) as total')
                ->where('user_status', 'Resume')
                ->whereBetween(DB::raw('DATE(created_at)'), [$start_date->toDateString(), $end_date->toDateString()]);

            $incomingQuery = DB::table('neox_call_logs')
                ->selectRaw('count(id) as total')
                ->whereRaw(" call_type = 'incoming'")
                ->whereBetween(DB::raw('DATE(created_at)'), [$start_date->toDateString(), $end_date->toDateString()])
                ->where('type', 'call_history');              

            // Outgoing Call Count
            $outgoingQuery = DB::table('neox_call_logs')
                ->selectRaw('count(id) as total')
                ->whereRaw(" call_type = 'outgoing'")
                ->whereBetween(DB::raw('DATE(created_at)'), [$start_date->toDateString(), $end_date->toDateString()])
                ->where('type', 'call_history');

            // Apply user filter if role_id is 3
            if ($user->role_id == 3) {
                $pauseQuery->where('user_id', $user->id);
                $resumeQuery->where('user_id', $user->id);
                $incomingQuery->where('caller_username', $user->username);
                $outgoingQuery->where('caller_username', $user->username);
            }

            // Execute queries
            $pauseCount = $pauseQuery->first();
            $resumeCount = $resumeQuery->first();
            $i_count = $incomingQuery->first();
            $o_count = $outgoingQuery->first();

            // Return the counts as a JSON response
            $data = [
                'pause_count' => $pauseCount->total,
                'resume_count' => $resumeCount->total,
                'incoming_count' => $i_count->total,
                'outgoing_count' => $o_count->total,
            ];            

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getGraphsRecord: ' . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
