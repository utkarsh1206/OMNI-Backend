<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PauseResumeApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Repositories\PauseResumeRepository;
use App\Services\ApiRequestService;
use Illuminate\Http\Request;

class PauseResumeApiController extends Controller
{
    private $apiRequestService;

    private $pauseResumeRepository;

    private $apiResponse;

    public function __construct(ApiRequestService $apiRequestService, PauseResumeRepository $pauseResumeRepository, CustomApiResponse $customApiResponse)
    {
        $this->apiRequestService = $apiRequestService;
        $this->pauseResumeRepository = $pauseResumeRepository;
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Post(
     *     path="/api/api_pause_resume",
     *     summary="Pause or Resume Agent",
     *     description="Handles pausing and resuming an agent's status.",
     *     tags={"Agent Pause Resume"},
     *     operationId="pauseResumeApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "action"},
     *                 @OA\Property(property="user_id", type="string", description="User ID of the agent", example="8002"),
     *                 @OA\Property(property="action", type="string", enum={"Pause", "Resume"}, description="Action type: Pause or Resume", example="Pause"),
     *                 @OA\Property(property="pause_code", type="string", description="Pause reason code (required if action is Pause)", nullable=true, example="")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated agent status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Action processed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="campaign_id", type="string"),
     *                 @OA\Property(property="user_status", type="string"),
     *                 @OA\Property(property="resume_time", type="string"),
     *                 @OA\Property(property="updated_at", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="total_pause_time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid request parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong, please try again later")
     *         )
     *     )
     * )
     */
    public function pauseResumeApi(Request $request)
    {
        $result = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_pause_resume',
            PauseResumeApiRequest::class
        );

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $result = $result->getData(true);
        }

        $userId = $request->id;
        $campaignId = $request->campaign_id;
        $action = $request->action;
        $pauseCode = $request->pause_code ?? null;
        $extension = $request->phone;

        if (isset($result['code']) && $result['code'] === 0) {
            $data = $this->pauseResumeRepository->logAction($userId, $campaignId, $extension, $action, $pauseCode);
            return $this->successResponse($data);
        }

        return $this->apiResponse->getResponseStructure(trans('message.error'), $result, trans('message.error'));
    }

    private function successResponse($data)
    {
        return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.success'));
    }
}
