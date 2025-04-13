<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DisposCallApiRequest;
use Illuminate\Http\Request;
use App\Repositories\DisposCallApiRepository;
use App\Services\ApiRequestService;

class DisposCallApiController extends Controller
{
    private $disposCallApiRepository;
    private $apiRequestService;

    public function __construct(ApiRequestService $apiRequestService, DisposCallApiRepository $disposCallApiRepository)
    {
        $this->disposCallApiRepository = $disposCallApiRepository;
        $this->apiRequestService = $apiRequestService;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/dispos-call",
     *     tags={"Disposition"},
     *     summary="Handle disposition after call ends",
     *     description="Processes the agent disposition input and updates related tables.",
     *     operationId="disposCallApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"disposition_status"},
     *                 @OA\Property(property="disposition_status", type="string", description="Disposition sort code", example="NI"),
     *                 @OA\Property(property="comment", type="string", description="Comment for disposition", example="User not interested."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Disposition processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="dispos_data", type="object"),
     *                 @OA\Property(property="live_call", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function disposCallApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_dispo_call',
            DisposCallApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->disposCallApiRepository->processDisposCall($apiResult, $request);
    }
}
