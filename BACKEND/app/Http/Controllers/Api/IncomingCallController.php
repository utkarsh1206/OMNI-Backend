<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\IncomingCallRepository;
use Illuminate\Http\Request;

class IncomingCallController extends Controller
{
    protected $incomingRepo;

    public function __construct(IncomingCallRepository $incomingRepo)
    {
        $this->incomingRepo = $incomingRepo;
    }

    /**
     * @OA\Get(
     *     path="/api/neox-call-incoming",
     *     tags={"Incoming Calls Check"},
     *     summary="Handle incoming call and logs",
     *     description="This endpoint processes incoming calls by updating or creating records in LiveCallLog, LiveAgents, NeoxTempCallLog and returns updated data along with a status flag outside data block.",
     *     operationId="checkIncoming",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Incoming call processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="status", type="string", example="Success"),
     *             @OA\Property(property="neox_status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="live_call_log", type="object", description="Details from LiveCallLog"),
     *                 @OA\Property(property="live_agent", type="object", description="Updated LiveAgent record"),
     *                 @OA\Property(property="temp_call_log", type="object", description="Updated NeoxTempCallLog record")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation or Processing Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error or failure reason"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong"),
     *             @OA\Property(property="status", type="string", example="error")
     *         )
     *     )
     * )
     */

    public function checkIncoming(Request $request)
    {
        $liveCall = $this->incomingRepo->handleIncomingCall($request);

        return $liveCall;
    }
}
