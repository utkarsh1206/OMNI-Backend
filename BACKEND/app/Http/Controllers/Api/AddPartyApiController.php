<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddPartyApiRequest;
use Illuminate\Http\Request;
use App\Repositories\AddPartyApiRepository;
use App\Services\ApiRequestService;

class AddPartyApiController extends Controller
{
    private $apiRequestService;
    private $addPartyRepo;

    public function __construct(ApiRequestService $apiRequestService, AddPartyApiRepository $addPartyRepo)
    {
        $this->apiRequestService = $apiRequestService;
        $this->addPartyRepo = $addPartyRepo;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/add-party",
     *     summary="Add a party to a live call (conference)",
     *     tags={"Conference"},
     *     operationId="addPartyApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"participant", "conf_type"},
     *             @OA\Property(property="participant", type="string", example=8003, description="User ID of the agent"),
     *             @OA\Property(property="conf_type", type="string", example="EXTERNAL", description="type of the conference")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Party added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or logical error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Agent not found or invalid data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *         )
     *     )
     * )
     */

    public function addPartyApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_addparty',
            AddPartyApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->addPartyRepo->handleAddParty($apiResult, $request);
    }
}
