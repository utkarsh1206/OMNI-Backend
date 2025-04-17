<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisconnectConfApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Repositories\DisconnectConfApiRepository;
use App\Services\ApiRequestService;
use Illuminate\Support\Facades\Validator;

class DisconnectConfApiController extends Controller
{
    private $apiRequestService;
    private $disconnectConf;

    public function __construct(ApiRequestService $apiRequestService, DisconnectConfApiRepository $disconnectConfApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->disconnectConf = $disconnectConfApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/disconnect-conf",
     *     tags={"Conference"},
     *     summary="Disconnect an ongoing conference call",
     *     description="Disconnects a live conference call for an agent and logs call details in the system.",
     *     operationId="disconnectConfApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conference disconnected successfully",
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
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */

    public function disconnectConfApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_disconnectconf',
            DisconnectConfApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->disconnectConf->handleDisconnectConf($apiResult, $request);
    }
}
