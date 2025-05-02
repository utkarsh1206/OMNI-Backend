<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCallBackRequest;
use App\Repositories\CallBackRepository;

/**
 * @OA\Tag(name="CallBacks")
 */
class CallBackController extends Controller
{
    protected $callBackRepository;

    public function __construct(CallBackRepository $callBackRepository)
    {
        $this->callBackRepository = $callBackRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/call-backs",
     *     summary="Create a new call back",
     *     description="Create a new call back entry with the provided lead and scheduling details.",
     *     operationId="createCallBack",
     *     tags={"CallBacks"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={
     *                     "lead_id", "list_id", "campaign_id", "user_id", 
     *                     "call_back_time", "recipient", "comments"
     *                 },
     *                 @OA\Property(property="lead_id", type="integer", example=101),
     *                 @OA\Property(property="list_id", type="integer", example=202),
     *                 @OA\Property(property="campaign_id", type="integer", example=303),
     *                 @OA\Property(property="user_id", type="integer", example=404),
     *                 @OA\Property(property="call_back_time", type="string", format="date-time", example="2025-05-03T14:30:00Z"),
     *                 @OA\Property(property="recipient", type="string", enum={"Useronly", "Anyone"}, example="Useronly"),
     *                 @OA\Property(property="comments", type="string", example="Please call in the afternoon"),
     *                 @OA\Property(property="user_group", type="string", example="sales"),
     *                 @OA\Property(property="lead_status", type="string", example="CALLBK"),
     *                 @OA\Property(property="last_modified_user", type="string", example="agent01")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Call back successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="lead_id", type="integer", example=101),
     *                 @OA\Property(property="list_id", type="integer", example=202),
     *                 @OA\Property(property="campaign_id", type="integer", example=303),
     *                 @OA\Property(property="user_id", type="integer", example=404),
     *                 @OA\Property(property="call_back_time", type="string", format="date-time", example="2025-05-03T14:30:00Z"),
     *                 @OA\Property(property="recipient", type="string", example="Useronly"),
     *                 @OA\Property(property="comments", type="string", example="Please call in the afternoon"),
     *                 @OA\Property(property="user_group", type="string", example="sales"),
     *                 @OA\Property(property="lead_status", type="string", example="CALLBK"),
     *                 @OA\Property(property="last_modified_user", type="string", example="agent01"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-02T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-02T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function store(StoreCallBackRequest $request)
    {
        return $this->callBackRepository->store($request);
    }
}
