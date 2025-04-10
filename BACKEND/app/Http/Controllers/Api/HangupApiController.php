<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HangupApiRequest;
use Illuminate\Http\Request;
use App\Repositories\HangupApiRepository;
use App\Services\ApiRequestService;

class HangupApiController extends Controller
{
    private $apiRequestService;
    private $hangupApiRepository;

    public function __construct(ApiRequestService $apiRequestService, HangupApiRepository $hangupApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->hangupApiRepository = $hangupApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/hangup",
     *     summary="Handle call hangup",
     *     description="This endpoint is triggered when call end. It updates logs, statuses, and other call-related records.",
     *     operationId="hangupApi",
     *     tags={"Hangup"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success or error message with call details",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=0),
     *             @OA\Property(property="message", type="string", example="Success.!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="message", type="string", example="Hangup processed successfully."),
     *                 @OA\Property(property="dispostion_time", type="string", format="date-time", example="2025-04-07 12:34:56"),
     *                 @OA\Property(property="call_log", type="object"),
     *                 @OA\Property(property="live_call", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid data."),
     *         )
     *     )
     * )
     */
    public function hangupApi(Request $request)
    {
        $result = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_hangup',
            HangupApiRequest::class
        );

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $result = $result->getData(true);
        }

        if (isset($result['code']) && $result['code'] === 0) {
            return $this->hangupApiRepository->handleSuccessfulHangup($request, $result);
        } else {
            return $this->hangupApiRepository->handleFailedHangup($request, $result);
        }
    }
}
