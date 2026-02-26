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
                // ->whereNotNull('logout_time')
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
    public function getData(Request $request, $connectionName = null)
    {
        try {
            $user_id = $request->input('user_id');
            $date = $request->input('date');

            // Fetch necessary data
            $log_data = $this->getLoginLogs($user_id, $date, $connectionName);
            $pause_resume_data = $this->getPauseResumeLogs($user_id, $date, $connectionName);
            $pause_resume_total = $this->getTotalPauseResumeTime($user_id, $date, $connectionName);
            $user_name = $this->getUserNameById($user_id, $connectionName);
            $i_call_data = $this->getCallData($user_name, $date, 'incoming', $connectionName);
            $o_call_data = $this->getCallData($user_name, $date, 'outgoing', $connectionName);
            $incoming = $this->getCallHistoryData('incoming', $user_name, $date, $connectionName);
            $outgoing = $this->getCallHistoryData('outgoing', $user_name, $date, $connectionName);
            $ready_data = $this->getReadyData($user_id, $date, $connectionName);
            $dash_data = $this->getReportData($user_id, '', $date, $connectionName);
            $todaysLoginData = $this->getTodaysLoginTime($request, $connectionName);
            $pause_resume_activity = $this->getActivityPauseData($user_id, $date, $connectionName);
            $daily_user_activity = $this->getDailyUserActivity($user_id, $date, $connectionName);
            $total_duration = $this->getTotalDuration($user_id, $date, $connectionName);

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
    private function getLoginLogs($user_id, $date, $connectionName = null)
    {
        try {
            return UserLoginLog::on($connectionName)->select('id', 'login_date', 'logout_date', 'login_time', 'logout_time')
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
    private function getPauseResumeLogs($user_id, $date, $connectionName = null)
    {
        try {
            return PauseResumeUserLog::on($connectionName)->select('id', 'pause_time', 'resume_time', 'user_status', 'pause_code_id')
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
    private function getTotalPauseResumeTime($user_id, $date, $connectionName = null)
    {
        try {
            return PauseResumeUserLog::on($connectionName)->selectRaw("SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))) AS total_resume, SEC_TO_TIME(SUM(TIME_TO_SEC(total_pause_time))) AS total_pause")
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
    private function getUserNameById($user_id, $connectionName = null)
    {
        try {
            return Users::on($connectionName)->where('id', $user_id)->pluck('username')->first();
        } catch (Exception $e) {
            generateLogs('error', 'Error in UserActivityReportRepository@getUserNameById: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get incoming or outgoing call data for the user
     */
    private function getCallData($user_name, $date, $call_type, $connectionName = null)
    {
        try {
            return DB::connection($connectionName)->table('neox_call_logs as nl')
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
    private function getCallHistoryData($call_type, $user_name, $date, $connectionName = null)
    {
        try {
            return NeoxCallLog::on($connectionName)->select(
                'caller_user_extension',
                'called_phone_number',
                DB::raw('SEC_TO_TIME(call_talk_duration) as call_talk_duration'),
                DB::raw('SEC_TO_TIME(CAST(call_total_duration AS UNSIGNED)) as call_total_duration'),
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
    private function getReadyData($user_id, $date, $connectionName = null)
    {
        try {
            return PauseResumeUserLog::on($connectionName)->selectRaw(
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
    public function getTodaysLoginTime(Request $request, $connectionName = null)
    {
        try {
            $userId = $request->input('user_id');
            $todayDate = Carbon::today()->toDateString();
            $totalLoginTime = '';
            $todaysLoginTime = false;

            // Get the latest login record for today
            $loginDetail = UserLoginLog::on($connectionName)->where('user_id', $userId)
                ->whereDate('login_date', $todayDate)
                ->orderBy('id', 'desc')
                ->first();

            if ($request->input('date') == $todayDate && $loginDetail) {
                if ($loginDetail->logout_time == '00:00:00' && $loginDetail->logout_date == '0000-00-00') {
                    // Get the first login time for today
                    $firstLoginTime = UserLoginLog::on($connectionName)->where('user_id', $userId)
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
    private function getDailyUserActivity($user_id, $date, $connectionName = null)
    {
        try {
            return UserDailyActivity::on($connectionName)->with('pauseCode')
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
    private function getTotalDuration($user_id, $date, $connectionName = null)
    {
        try {
            return UserDailyActivity::on($connectionName)->selectRaw("
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
    public function getReportData($userId, $campaignId = '', $date, $connectionName = null)
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

            $result = DB::connection($connectionName)->table('users as u')
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
    public function getActivityPauseData($user_id, $date, $connectionName = null)
    {
        try {
            return PauseResumeUserLog::on($connectionName)->select(
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
    public function exportReportToCsv(Request $request, $connectionName = null, $isService = false)
    {
        try {
            $selectedColumns = $request->input('selected_columns', []);

            if ($request->has('startDate') && $request->has('endDate')) {
                $startDatetime = Carbon::parse($request->input('startDate'));
                $endDatetime = Carbon::parse($request->input('endDate'));
            }

            // Get authenticated user
            if ($isService == false) {
                $user = auth()->user();
            }

            $logs = UserLoginLog::on($connectionName)->select([
                'user_id',
                'login_date',
                DB::raw("SUM(TIMESTAMPDIFF(SECOND, CONCAT(login_date, ' ', login_time), CONCAT(logout_date, ' ', logout_time))) as total_seconds"),
                DB::raw("COUNT(*) as login_count")
            ])
                // ->whereNotNull('logout_time')
                ->whereBetween('login_date', [$startDatetime, $endDatetime])
                ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
                ->when(!$isService && $user && $user->role_id == 3, fn($q) => $q->where('user_id', $user->id))
                ->groupBy('login_date', 'user_id')
                ->with('user')
                ->orderByDesc('login_date')->limit(300)->get();

            if ($logs->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            $exportableColumns = [
                'Date' => fn($log) => $log->login_date,
                'User Name' => fn($log) =>
                optional($log->user)->username,
                'Total Duration' => fn($log) =>
                gmdate('H:i:s', $log->total_seconds ?? 0),
                'Login Count' => fn($log) =>
                $log->login_count ?? 0,
            ];

            $validColumnsMap = array_flip(array_keys($exportableColumns));

            $selectedColumns = array_values(array_filter(
                $selectedColumns,
                fn($column) => isset($validColumnsMap[$column])
            ));

            if (empty($selectedColumns)) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'No valid columns selected for export'
                );
            }

            $groupedData = $logs->groupBy(['login_date', 'user_id']);

            $userIds   = $logs->pluck('user_id')->unique();
            $dates     = $logs->pluck('login_date')->unique();
            $usernames = $logs->pluck('user.username', 'user_id');

            // CSV headers
            // $csvHeader = ['Date', 'Username', 'Total Duration', 'Login Count'];
            $csvHeader = $selectedColumns;

            $startDate = Carbon::parse($request->startDate)->toDateString();
            $endDate   = Carbon::parse($request->endDate)->toDateString();

            $loginData = DB::connection($connectionName)
                ->table('user_login_logs')
                ->selectRaw("
                    user_id,
                    login_date,
                    COUNT(*) as login_count,
                    MIN(CONCAT(login_date,' ',login_time)) as first_login,
                    MAX(CONCAT(login_date,' ',login_time)) as last_log,
                    MAX(CONCAT(logout_date,' ',logout_time)) as last_logout,
                    SUM(
                        TIMESTAMPDIFF(
                            SECOND,
                            CONCAT(login_date,' ',login_time),
                            CONCAT(logout_date,' ',logout_time)
                        )
                    ) as total_log_sec
                ")
                ->whereBetween('login_date', [$startDate, $endDate])
                ->groupBy('user_id', 'login_date')
                ->get();

                $loginLogs = $loginData->keyBy(function ($i) {
                    return Carbon::parse($i->login_date)->toDateString() . '_' . $i->user_id;
                });

            $callData = DB::connection($connectionName)
                ->table('neox_call_logs')
                ->selectRaw("
                    caller_username,
                    DATE(created_at) as activity_date,
                    COUNT(*) as calls,
                    SUM(call_type = 'outgoing') as outgoing_call,
                    SUM(call_type = 'incoming') as incoming_call,
                    SUM(type = 'CALL_CONNECTED') as answered,
                    SUM(call_talk_duration) as total_talk_sec,
                    AVG(call_talk_duration) as talk_avg_sec
                ")
                ->whereBetween('created_at', [$startDatetime, $endDatetime])
                ->where('type', 'call_history')
                ->groupBy('caller_username', 'activity_date')
                ->get();

                $usernameToUserId = $usernames->flip();

                $callLogs = $callData->keyBy(function ($i) use ($usernameToUserId) {
                    $userId = $usernameToUserId[$i->caller_username] ?? null;
                
                    return Carbon::parse($i->activity_date)->toDateString() . '_' . $userId;
                });

            // Pause Resume
            $pauseResume = PauseResumeUserLog::on($connectionName)
                ->whereIn('user_id', $userIds)
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->get()
                ->groupBy(
                    fn($i) =>
                    Carbon::parse($i->created_at)->toDateString() . '_' . $i->user_id
                );

            // Pause Resume Totals
            $pauseTotals = PauseResumeUserLog::on($connectionName)
                ->selectRaw("
                user_id,
                DATE(created_at) as d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_pause_time))) as total_pause,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))) as total_resume
            ")
                ->whereIn('user_id', $userIds)
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->groupBy('user_id', 'd')
                ->get()
                ->keyBy(fn($i) => $i->d . '_' . $i->user_id);

            // Ready Data
            $readyData = PauseResumeUserLog::on($connectionName)
                ->selectRaw("
                user_id,
                DATE(created_at) as d,
                TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(total_resume_time))), '%H:%i:%s') as ready_avg,
                TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(total_resume_time))), '%H:%i:%s') as ready
            ")
                ->whereIn('user_id', $userIds)
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->groupBy('user_id', 'd')
                ->get()
                ->keyBy(fn($i) => $i->d . '_' . $i->user_id);

            // Call History
            $callHistory = NeoxCallLog::on($connectionName)
                ->whereIn('caller_username', $usernames->values())
                ->whereIn(DB::raw('DATE(call_date)'), $dates)
                ->where('type', 'call_history')
                ->get()
                ->groupBy(
                    fn($i) =>
                    Carbon::parse($i->call_date)->toDateString()
                        . '_' . $usernames->search($i->caller_username)
                        . '_' . $i->call_type
                );

            // Daily Activity
            $dailyActivity = UserDailyActivity::on($connectionName)
                ->with('pauseCode')
                ->whereIn('user_id', $userIds)
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->get()
                ->groupBy(
                    fn($i) =>
                    Carbon::parse($i->created_at)->toDateString() . '_' . $i->user_id
                );

            // Total Duration
            $totalDuration = UserDailyActivity::on($connectionName)
                ->selectRaw("
                user_id,
                DATE(created_at) as d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(total_login_duration))) AS total_login_d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(pause_duration))) AS total_pause_d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(resume_duration))) AS total_resume_d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(call_duration))) AS total_call_d,
                SEC_TO_TIME(SUM(TIME_TO_SEC(dispo_duration))) AS total_dispose_d
            ")
                ->whereIn('user_id', $userIds)
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->groupBy('user_id', 'd')
                ->get()
                ->keyBy(fn($i) => $i->d . '_' . $i->user_id);

            $loginLogoutLogs = UserLoginLog::on($connectionName)
                ->whereIn('user_id', $userIds)
                ->whereIn('login_date', $dates)
                ->get();

            $csvRows = [];

            foreach ($groupedData as $date => $users) {
                foreach ($users as $userId => $entries) {
                    $key = $date . '_' . $userId;

                    $logData = $loginLogs[$key] ?? null;
                    $pauseData     = $pauseResume[$key] ?? collect();
                    $pauseTotal    = $pauseTotals[$key] ?? null;
                    $ready         = $readyData[$key] ?? null;
                    $daily         = $dailyActivity[$key] ?? collect();
                    $total         = $totalDuration[$key] ?? null;
                    $incoming      = $callHistory[$key . '_incoming'] ?? collect();
                    $outgoing      = $callHistory[$key . '_outgoing'] ?? collect();
                    $call = $callLogs[$key] ?? null;

                    $totalDuration = 0;

                    $row = [];

                    $log = $entries->first();

                    foreach ($selectedColumns as $column) {
                        $row[] = $exportableColumns[$column]($log);
                    }

                    $csvRows[] = $row;

                    $csvRows[] = ['--- DETAILED ACTIVITY DATA ---'];
                    $csvRows[] = ['First Login', 'Last Logout', 'Total Log Time'];
                    $csvRows[] = [
                        $logData->first_login ?? 'Not Logged',
                        $logData->last_logout ?? 'Not Logged',
                        isset($logData->total_log_sec)
                            ? gmdate('H:i:s', $logData->total_log_sec)
                            : '00:00:00'
                    ];

                    $csvRows[] = [''];
                    $csvRows[] = ['Total Talk', 'Talk Average', 'Total Pause Time', 'Pause Average', 'Total Ready Time', 'Ready Average'];
                    $csvRows[] = [
                        isset($call->total_talk_sec)
                            ? gmdate('H:i:s', $call->total_talk_sec)
                            : '00:00:00',

                        isset($call->talk_avg_sec)
                            ? gmdate('H:i:s', $call->talk_avg_sec)
                            : '00:00:00',

                        $pauseTotal->total_pause ?? '00:00:00',

                        // If you don’t have pause average separately:
                        isset($pauseTotal->total_pause)
                            ? $pauseTotal->total_pause
                            : '00:00:00',

                        $ready->ready ?? '00:00:00',

                        $ready->ready_avg ?? '00:00:00'
                    ];

                    $csvRows[] = [''];
                    $csvRows[] = ['--- INCOMING DATA ---'];
                    $csvRows[] = ['Caller Number', 'Call Time', 'Call Duration', 'Dispo Stat'];
                    if ($incoming->isNotEmpty()) {
                        foreach ($incoming as $i) {
                            $csvRows[] = [
                                $i->called_phone_number ?? '',
                                isset($i->call_date)
                                    ? Carbon::parse($i->call_date)
                                    ->setTimezone(env('APP_CSVTIMEZONE'))
                                    ->format('Y-m-d H:i:s')
                                    : '',
                                isset($i->call_talk_duration)
                                    ? gmdate('H:i:s', $i->call_talk_duration)
                                    : '00:00:00',
                                $i->call_dispo_status ?? ''
                            ];
                        }
                    } else {
                        $csvRows[] = ['No Incoming Data'];
                    }

                    $csvRows[] = [''];
                    $csvRows[] = ['--- OUTGOING DATA ---'];
                    $csvRows[] = ['Caller Number', 'Call Time', 'Call Duration', 'Dispo Stat'];
                    if ($outgoing->isNotEmpty()) {
                        foreach ($outgoing as $o) {
                            $csvRows[] = [
                                $o->called_phone_number ?? '',
                                isset($o->call_date)
                                    ? Carbon::parse($o->call_date)
                                    ->setTimezone(env('APP_CSVTIMEZONE'))
                                    ->format('Y-m-d H:i:s')
                                    : '',
                                isset($o->call_talk_duration)
                                    ? gmdate('H:i:s', $o->call_talk_duration)
                                    : '00:00:00',
                                $o->call_dispo_status ?? ''
                            ];
                        }
                    } else {
                        $csvRows[] = ['No Outgoing Data'];
                    }

                    $csvRows[] = [''];
                    $csvRows[] = ['--- DAILY USER ACTIVITY ---'];
                    $csvRows[] = ['Date/Time', 'Activity', 'Total Login Duration', 'Pause Duration', 'Pause Code', 'Resume Duration', 'Call Duration', 'Dispose Duration', 'Dispose Code'];
            
                    if ($daily->isNotEmpty()) {

                        foreach ($daily as $activity) {

                            $csvRows[] = [
                                isset($activity->created_at)
                                    ? Carbon::parse($activity->created_at)
                                    ->setTimezone(env('APP_CSVTIMEZONE'))
                                    ->format('Y-m-d H:i:s')
                                    : '',

                                $activity->activity ?? '',

                                $activity->total_login_duration ?? '00:00:00',

                                $activity->pause_duration ?? '00:00:00',

                                optional($activity->pauseCode)->pause_code ?? '',

                                $activity->resume_duration ?? '00:00:00',

                                $activity->call_duration ?? '00:00:00',

                                $activity->dispo_duration ?? '00:00:00',

                                $activity->dispo_code ?? ''
                            ];
                        }
                    } 

                    $csvRows[] = ['--- TOTAL DURATIONS ---'];
                    $csvRows[] = [
                        'Total Login Duration',
                        'Total Pause Duration',
                        'Total Resume Duration',
                        'Total Call Duration',
                        'Total Disposition Duration'
                    ]; 
                    $csvRows[] = [
                        $total->total_login_d ?? '00:00:00',
                        $total->total_pause_d ?? '00:00:00',
                        $total->total_resume_d ?? '00:00:00',
                        $total->total_call_d ?? '00:00:00',
                        $total->total_dispose_d ?? '00:00:00'
                    ];

                    $csvRows[] = [''];
                    $csvRows[] = ['--- LOGIN/LOGOUT TIME ---'];
                    $csvRows[] = ['Login Time', 'Logout Time'];
                    if ($loginLogoutLogs->isNotEmpty()) {
                        foreach ($loginLogoutLogs as $log) {
                            $csvRows[] = [
                                $log->login_time ?? 'Closed Browser',
                                $log->logout_time ?? 'Closed Browser'
                            ];
                        }
                    }

                    $csvRows[] = [''];
                    $csvRows[] = ['--- PAUSE/RESUME ACTIVITY ---'];
                    $csvRows[] = ['Activity', 'Time', 'Reason'];
                    if ($pauseData->isNotEmpty()) {

                        foreach ($pauseData as $p) {
                    
                            $csvRows[] = [
                                $p->user_status ?? '',
                                Carbon::parse($p->created_at)
                                    ->setTimezone(env('APP_CSVTIMEZONE'))
                                    ->format('Y-m-d H:i:s'),
                                $p->pause_code_id ?? ''
                            ];
                        }
                    
                    }

                    $csvRows[] = [''];
                    $csvRows[] = ['--- TOTAL INCOMING/OUTGOING ---'];
                    $csvRows[] = ['Total Incoming', 'Total Outgoing'];
                    $csvRows[] = [
                        $incoming->count(),
                        $outgoing->count()
                    ];

                    $csvRows[] = [''];
                    $csvRows[] = ['--- END OF DETAILED DATA ---'];
                    $csvRows[] = [''];

                }
            }

            // ✅ Save CSV file using your helper
            $fileUrl = exportCsv($csvHeader, $csvRows, 'user_activity_report');

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                ['file_url' => $fileUrl],
                trans('message.report_generated')
            );
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
