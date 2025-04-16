<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CallTransferApiRequest;
use Illuminate\Http\Request;
use App\Repositories\CallTransferApiRepository;
use App\Services\ApiRequestService;

class CallTransferApiController extends Controller
{
    private $apiRequestService;
    private $callTransferRepo;

    public function __construct(
        ApiRequestService $apiRequestService,
        CallTransferApiRepository $callTransferRepo
    ) {
        $this->apiRequestService = $apiRequestService;
        $this->callTransferRepo = $callTransferRepo;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/call-tansfer",
     *     summary="Transfer an ongoing call",
     *     description="This API is used to transfer an active call to another agent (direct) or group (ingroup), or even to a phone number.",
     *     operationId="callTransferApi",
     *     tags={"Call Transfer"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="callroute", type="string", enum={"IN_GROUP"}, example="IN_GROUP", description="The routing is to be done on an ingroup for specific agent or group transfer."),
     *             @OA\Property(
     *                 property="callroutedata",
     *                 type="string",
     *                 example="AGENTDIRECT",
     *                 description="The ingroup name where the call is to be transferred. Use 'AGENTDIRECT' for specific agent transfer, or ingroup name for group transfer. Required if callroute is 'IN_GROUP'."
     *             ),
     *             @OA\Property(
     *                 property="transfer_target_user_id",
     *                 type="string",
     *                 example="U9002",
     *                 description="User ID of the agent to transfer to. Required if callroutedata = 'AGENTDIRECT'."
     *             ),
     *             @OA\Property(
     *                 property="transfer_target_phone_number",
     *                 type="string",
     *                 example="1234567890",
     *                 description="External phone number for call transfer. Required if transferring to an external number (extension or PSTN)."
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call transfer log created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call transfer logged successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */

    public function callTransferApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_call_transfer',
            CallTransferApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->callTransferRepo->handleCallTransfer($apiResult, $request);
    }
}
