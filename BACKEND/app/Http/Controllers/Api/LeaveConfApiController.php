<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveConfApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Repositories\LeaveApiRepository;
use App\Services\ApiRequestService;
use Illuminate\Support\Facades\Validator;

class LeaveConfApiController extends Controller
{
    private $apiRequestService;
    private $leaveApiRepository;

    public function __construct(ApiRequestService $apiRequestService, LeaveApiRepository $leaveApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->leaveApiRepository = $leaveApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/leave-conf",
     *     tags={"Conference"},
     *     summary="Leave a conference call",
     *     description="Allows an agent to leave an ongoing conference call. Logs the action and updates call records.",
     *     operationId="leaveConfApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Agent left the conference successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or agent not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */

    public function leaveConfApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_leaveconf',
            LeaveConfApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->leaveApiRepository->handleLeaveConf($apiResult, $request);
    }
}
