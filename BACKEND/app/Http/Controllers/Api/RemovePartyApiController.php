<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RemovePartyApiRequest;
use App\Repositories\RemovePartyApiRepository;
use Illuminate\Http\Request;
use App\Services\ApiRequestService;

class RemovePartyApiController extends Controller
{
    private $apiRequestService;
    private $removePartyApiRepository;

    public function __construct(ApiRequestService $apiRequestService, RemovePartyApiRepository $removePartyApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->removePartyApiRepository = $removePartyApiRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/remove-party",
     *     tags={"Conference"},
     *     summary="Remove a party from the conference call",
     *     description="Removes a participant from the ongoing conference and logs the activity.",
     *     operationId="removePartyApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Party removed successfully from the conference",
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

    public function removePartyApi(Request $request)
    {
        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_removeparty',
            RemovePartyApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->removePartyApiRepository->handleRemoveParty($apiResult, $request);
    }
}
