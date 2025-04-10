<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DialCallApiRequest;
use App\Repositories\DialCallApiRepository;
use App\Services\ApiRequestService;

class DialCallApiController extends Controller
{
    private $apiRequestService;
    private $dialCallApiRepository;

    public function __construct(ApiRequestService $apiRequestService, DialCallApiRepository $dialCallApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->dialCallApiRepository = $dialCallApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/dial-call",
     *     summary="Agent Dial Call",
     *     description="Handles Call Dialing.",
     *     tags={"Agent Dial Call"},
     *     operationId="dialCallApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id", "dialed_phone_number"},
     *                 @OA\Property(property="user_id", type="string", description="User ID of the agent", example="8002"),
     *                 @OA\Property(property="dialed_phone_number", type="string", description="Dialed Phone Number", example="333110"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully Call Dialed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call Dialing processed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="agent_extension", type="string"),
     *                 @OA\Property(property="campaign_id", type="string"),
     *                 @OA\Property(property="call_unique_id", type="string")
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

    public function dialCallApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_dial_call',
            DialCallApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->dialCallApiRepository->processDialCall($apiResult, $request);
    }
}
