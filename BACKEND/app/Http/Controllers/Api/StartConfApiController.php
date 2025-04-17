<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartConfApiRequest;
use Illuminate\Http\Request;
use App\Repositories\StartConfApiRepository;
use App\Services\ApiRequestService;

class StartConfApiController extends Controller
{
    private $apiRequestService;
    private $startConfRepository;

    public function __construct(ApiRequestService $apiRequestService, StartConfApiRepository $startConfRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->startConfRepository = $startConfRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/start-conf",
     *     tags={"Conference"},
     *     summary="Start a conference call",
     *     description="Initiates a conference call by logging the agent activity and updating the live call details.",
     *     operationId="startConfApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conference started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Success"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed or agent not found",
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

    public function startConfApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_startconf',
            StartConfApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->startConfRepository->handleStartConf($apiResult, $request);
    }
}
