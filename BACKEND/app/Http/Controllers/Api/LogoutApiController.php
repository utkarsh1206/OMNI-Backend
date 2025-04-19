<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\UserDailyActivity;
use App\Models\UserLoginLog;
use App\Services\ApiRequestService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class LogoutApiController extends Controller
{
    private $apiResponse;
    private $apiRequestService;

    public function __construct(CustomApiResponse $customApiResponse, ApiRequestService $apiRequestService)
    {
        $this->apiResponse = $customApiResponse;
        $this->apiRequestService = $apiRequestService;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/logout",
     *     summary="Logout an agent from Neox",
     *     description="Handles agent logout and logs user activity including login duration.",
     *     tags={"Neox Logout"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=0),
     *             @OA\Property(property="message", type="string", example="Logout successful"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */

    public function agentLogoutApi(Request $request)
    {
        try {
            $result = $this->apiRequestService->handleApiRequest(
                $request,
                'agent_logout',
                LogoutApiRequest::class,
            );

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $result = $result->getData(true);
            }

            if (isset($result['code']) && $result['code'] === 0) {
                $this->updateUserLoginLog($request->id, $request->campaign_id);
                $duration = $this->calculateLoginDuration($request->id, $request->campaign_id);

                $activityData = [
                    'campaign_id' => $request->campaign_id,
                    'user_id' => $request->id,
                    'activity' => 'Logout',
                    'total_login_duration' => $duration,
                ];

                UserDailyActivity::create($activityData);

                return (response()->json($result))->original + ['neox_status' => true];
            }

            return $result;
        } catch (Exception $e) {
            generateLogs('error', 'Logout processing failed: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function updateUserLoginLog($userId, $campaignId)
    {
        $latestLog = UserLoginLog::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->orderByDesc('login_date')
            ->orderByDesc('login_time')
            ->first();

        if ($latestLog) {
            $latestLog->update([
                'logout_date' => Carbon::today()->toDateString(),
                'logout_time' => Carbon::now()->toTimeString(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function calculateLoginDuration($userId, $campaignId)
    {
        $loginActivity = UserDailyActivity::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->where('activity', 'Login')
            ->whereDate('created_at', Carbon::today())
            ->orderByDesc('id')
            ->first();

        if ($loginActivity) {
            $diffInSeconds = Carbon::parse($loginActivity->created_at)->diffInSeconds(Carbon::now());

            return gmdate('H:i:s', $diffInSeconds);
        }

        return null;
    }
}
