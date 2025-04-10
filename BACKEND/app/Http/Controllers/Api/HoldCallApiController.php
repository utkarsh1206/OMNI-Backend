<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HoldCallApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Repositories\HoldApiRepository;
use App\Services\ApiRequestService;
use Illuminate\Support\Facades\Validator;

class HoldCallApiController extends Controller
{
    private $apiRequestService;
    private $holdApiRepository;

    public function __construct(ApiRequestService $apiRequestService, HoldApiRepository $holdApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->holdApiRepository = $holdApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/hold-call",
     *     summary="Hold or Unhold a Call",
     *     operationId="holdCallApi",
     *     tags={"Call Hold/Unhold"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  type="object",
     *                  required={"action"},
     *                  @OA\Property(property="action", type="string", example="Park"),
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="hold_log", type="object"),
     *                 @OA\Property(property="call_log", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid Request"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function holdCallApi(Request $request)
    {
        $result = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_hold',
            HoldCallApiRequest::class
        );

        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $result = $result->getData(true);
        }

        return $this->holdApiRepository->processHoldAction($result, $request);
    }
}
