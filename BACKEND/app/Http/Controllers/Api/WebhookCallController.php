<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\WebhookCallRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\NeoxCallLog;
use App\Repositories\WebhookCallRepository;
use Exception;

class WebhookCallController extends Controller
{
    protected $webhookCallRepository;
    private $apiResponse;

    // Inject WebhookCallRepository into the controller
    public function __construct(WebhookCallRepository $webhookCallRepository, CustomApiResponse $customApiResponse)
    {
        $this->webhookCallRepository = $webhookCallRepository;
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Post(
     *     path="/api/call_initiate",
     *     summary="Handle call initiation",
     *     description="Logs the initiation of a call",
     *     operationId="callInitiated",
     *     tags={"Webhook Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Logs the initiation of a call.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             type="object",
     *             required={"call_unique_id"},
     *              @OA\Property(property="call_unique_id", type="string", example="CI123456"),
     *             @OA\Property(property="campaign_id", type="string", example="CMP001"),
     *             @OA\Property(property="caller_username", type="string", example="agent1"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="1001"),
     *             @OA\Property(property="user_group", type="string", example="Sales"),
     *             @OA\Property(property="inbound_group_id", type="string", example="IG001"),
     *             @OA\Property(property="called_phone_number", type="string", example="567890")
     *         )
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call initiated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call data created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */
    // Handle call initiation
    public function callInitiated(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'CALL_INITIATED');
    }

    /**
     * @OA\Post(
     *     path="/api/call_connected",
     *     summary="Handle call connected",
     *     description="Logs the connected of a call",
     *     operationId="callConnected",
     *     tags={"Webhook Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Logs the connected of a call.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             type="object",
     *             required={"call_unique_id"},
     *              @OA\Property(property="call_unique_id", type="string", example="CI123456"),
     *             @OA\Property(property="campaign_id", type="string", example="CMP001"),
     *             @OA\Property(property="caller_username", type="string", example="agent1"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="1001"),
     *             @OA\Property(property="user_group", type="string", example="Sales"),
     *             @OA\Property(property="inbound_group_id", type="string", example="IG001"),
     *             @OA\Property(property="called_phone_number", type="string", example="567890")
     *         )
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call initiated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call data created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */
    // Handle call connection
    public function callConnected(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'CALL_CONNECTED');
    }


    /**
     * @OA\Post(
     *     path="/api/call_hangup",
     *     summary="Handle call Hangup",
     *     description="Logs the hangup of a call",
     *     operationId="callHangup",
     *     tags={"Webhook Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Logs the hangup of a call.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *             type="object",
     *             required={"call_unique_id"},
     *              @OA\Property(property="call_unique_id", type="string", example="CI123456"),
     *             @OA\Property(property="campaign_id", type="string", example="CMP001"),
     *             @OA\Property(property="caller_username", type="string", example="agent1"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="1001"),
     *             @OA\Property(property="user_group", type="string", example="Sales"),
     *             @OA\Property(property="inbound_group_id", type="string", example="IG001"),
     *             @OA\Property(property="called_phone_number", type="string", example="567890")
     *         )
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call hangup successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call data created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */
    // Handle call hangup
    public function callHangup(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'CALL_HANGUP');
    }

    /**
     * @OA\Post(
     *     path="/api/call_history",
     *     summary="Handle call history",
     *     description="Logs the history of a call",
     *     operationId="callHistory",
     *     tags={"Webhook Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Logs the history of a call.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"call_unique_id"},
     *                 @OA\Property(property="call_unique_id", type="string", example="CI123456"),
     *                 @OA\Property(property="campaign_id", type="string", example="CMP001"),
     *                 @OA\Property(property="caller_username", type="string", example="agent1"),
     *                 @OA\Property(property="caller_user_extension", type="integer", example="1001"),
     *                 @OA\Property(property="user_group", type="string", example="Sales"),
     *                 @OA\Property(property="inbound_group_id", type="string", example="IG001"),
     *                 @OA\Property(property="called_phone_number", type="string", example="567890"),
     *                 @OA\Property(property="call_total_duration", type="integer", example=180),
     *                 @OA\Property(property="call_talk_duration", type="integer", example=150),
     *                 @OA\Property(property="call_status", type="string", example="Completed"),
     *                 @OA\Property(property="call_dispo_status", type="string", example="Answered"),
     *                 @OA\Property(property="call_term_reason", type="string", example="AGENT"),
     *                 @OA\Property(property="comments", type="string", example="Customer was satisfied"),
     *                 @OA\Property(property="call_recording_url", type="string", example="https://example.com/recordings/CI123456.mp3")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call history logged successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Call data created successfully."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */
    // Handle call history
    public function callHistory(Request $request)
    {
        if ($request->has('call_unique_id')) {
            $callType = $this->determineCallTypeFromHistory($request);
            $data = $this->prepareHistoryData($request, $callType);

            // Update or create call history using the repository
            $callHistory = $this->webhookCallRepository->updateOrCreateCallHistory($data);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $callHistory,
                trans('message.call_data_created')
            );
        }

        return $this->sendErrorResponse();
    }

    // Common method to handle call requests (initiated, connected, hangup)
    private function handleCallRequest(Request $request, string $type)
    {
        try {
            if ($request->has('call_unique_id')) {
                $callType = $this->determineCallType($request);
                $data = $this->prepareCallData($request, $type, $callType);
                $callLog = $this->saveCallLogs($data);

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $callLog,
                    trans('message.call_data_created')
                );
            }

            return $this->sendErrorResponse();
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    // Determine call type (incoming or outgoing)
    private function determineCallType(Request $request)
    {
        return $request->has('inbound_group_id') ? 'incoming' : 'outgoing';
    }

    // Determine call type for history based on unique ID prefix
    private function determineCallTypeFromHistory(Request $request)
    {
        $uniqueId = strtoupper(substr($request->input('call_unique_id'), 0, 2));
        switch ($uniqueId) {
            case 'CI':
            case 'DP':
                return 'incoming';
            case 'HY':
                return 'outgoing';
            case 'VB':
                return 'vb';
            default:
                return 'unknown';
        }
    }

    // Prepare the common data for call log
    private function prepareCallData(Request $request, string $type, string $callType)
    {
        return [
            'call_unique_id' => $request->input('call_unique_id'),
            'campaign_id' => $request->input('campaign_id'),
            'caller_username' => $request->input('caller_username'),
            'caller_user_extension' => $request->input('caller_user_extension'),
            'user_group' => $request->input('user_group'),
            'inbound_group_id' => $request->input('inbound_group_id'),
            'called_phone_number' => $request->input('called_phone_number'),
            'call_date' => now(),
            'type' => $type,
            'call_type' => $callType,
            'json_data' => json_encode($request->all()),
        ];
    }

    // Prepare the data for call history
    private function prepareHistoryData(Request $request, string $callType)
    {
        return [
            'call_unique_id' => $request->input('call_unique_id'),
            'campaign_id' => $request->input('campaign_id'),
            'caller_username' => $request->input('caller_username'),
            'caller_user_extension' => $request->input('caller_user_extension'),
            'user_group' => $request->input('user_group'),
            'inbound_group_id' => $request->input('inbound_group_id'),
            'called_phone_number' => $request->input('called_phone_number') ?? null,
            'call_date' => now(),
            'call_total_duration' => $request->input('call_total_duration'),
            'call_talk_duration' => $request->input('call_talk_duration'),
            'call_status' => $request->input('call_status'),
            'call_dispo_status' => $request->input('call_dispo_status') ?? null,
            'call_term_reason' => $request->input('call_term_reason'),
            'comments' => $request->input('comments'),
            'call_recording_url' => $request->input('call_recording_url') ?? null,
            'type' => 'CALL_HISTORY',
            'call_type' => $callType,
            'json_data' => json_encode($request->all())
        ];
    }

    // Save the call data to both main and temporary logs
    private function saveCallLogs(array $data)
    {
        // Save to neox_call_logs table
        $callLog = $this->webhookCallRepository->saveCallLog($data);

        // Prepare and save to neox_temp_call_logs
        $tempData = array_merge($data, [
            'DATE' => now()->toDateString(),
            'TIME' => now()->toTimeString()
        ]);
        $this->webhookCallRepository->saveTempCallLog($tempData);

        return $callLog;
    }

    // Handle sending error response
    private function sendErrorResponse()
    {
        // return response()->json([
        //     'error_code' => 'STL-0X311',
        //     'msg' => 'Not accessible for outer source',
        // ], 400);

        return $this->apiResponse->getResponseStructure(
            trans('message.error'),
            null,
            trans('message.not_accessible')
        );
    }
}
