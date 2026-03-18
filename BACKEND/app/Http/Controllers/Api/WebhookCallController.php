<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\WebhookCallRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\Users;
use App\Repositories\WebhookCallRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\LiveAgents;
use App\Events\AgentLiveStatus;
use App\Models\Contact;
use App\Models\Detail;
use App\Models\UserDailyActivity;

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
     *     path="/api/call-initiate",
     *     summary="Handle call initiation",
     *     description="Logs the initiation of a call",
     *     operationId="callInitiated",
     *     tags={"Webhook Calls"},
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
     *             @OA\Property(property="caller_username", type="string", example="omniagent2"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="8002"),
     *             @OA\Property(property="user_group", type="string", example="Sales"),
     *             @OA\Property(property="inbound_group_id", type="string", example="IG001"),
     *             @OA\Property(property="called_phone_number", type="string", example="567890"),
     *             @OA\Property(property="domain_name", type="string", example="Tenant4000")
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
    public function callInitiated(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'call_initiated');
    }

    /**
     * @OA\Post(
     *     path="/api/call-connected",
     *     summary="Handle call connected",
     *     description="Logs the connected of a call",
     *     operationId="callConnected",
     *     tags={"Webhook Calls"},
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
     *             @OA\Property(property="caller_username", type="string", example="omniagent2"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="8002"),
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
    public function callConnected(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'call_connected');
    }

    /**
     * @OA\Post(
     *     path="/api/call-hangup",
     *     summary="Handle call Hangup",
     *     description="Logs the hangup of a call",
     *     operationId="callHangup",
     *     tags={"Webhook Calls"},
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
     *             @OA\Property(property="caller_username", type="string", example="omniagent2"),
     *             @OA\Property(property="caller_user_extension", type="intiger", example="8002"),
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
    public function callHangup(WebhookCallRequest $request)
    {
        return $this->handleCallRequest($request, 'call_hangup');
    }

    /**
     * @OA\Post(
     *     path="/api/call-history",
     *     summary="Handle call history",
     *     description="Logs the history of a call",
     *     operationId="callHistory",
     *     tags={"Webhook Calls"},
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
     *                 @OA\Property(property="caller_username", type="string", example="omniagent2"),
     *                 @OA\Property(property="caller_user_extension", type="integer", example="8002"),
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
    public function callHistory(Request $request)
    {
        if ($request->has('call_unique_id')) {
            $callType = $this->determineCallTypeFromHistory($request);
            $data = $this->prepareHistoryData($request, $callType);

            $domain_name = $request->domain_name;
            $connection = getDbConnection($domain_name);

            // Update or create call history using the repository
            $callHistory = $this->webhookCallRepository->updateOrCreateCallHistory($data, $connection);
            generateLogs("info", "Call History Webhook Initiated: " . json_encode($callHistory));
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
            $domain_name = $request->domain_name;
            $domain = strtolower(preg_replace('/[[:punct:]]+/', '_', $domain_name));

            if ($request->has('call_unique_id')) {
                getDbConnection($domain_name, true);
                // DB::setDefaultConnection($data->getName());

                $callUniqueId = $request->input('call_unique_id');
                // $isSOT = (strpos($callUniqueId, 'SOT') === 0);

                $ids = ['agent_id' => null, 'parent_id' => null];

                $agentUser = Users::select('id')->where('username', $request->input('caller_username'))->first();
                $ids['agent_id'] = $agentUser?->id;

                // if ($isSOT) {
                //     $parentUser = Users::select('id')->where('username', $request->input('parent_user_id'))->first();
                //     $ids['parent_id'] = $parentUser?->id;
                // }

                $confState = $request->input('conf_state') ?: null;
                $callType = $this->determineCallType($request);
                $data = $this->prepareCallData($request, $type, $callType);

                // generateLogs("info", "\n\n\n Call Unique ID: " . $callUniqueId);
                // generateLogs("info", "\n Conf State: " . $confState);
                // generateLogs("info", "\n Call Type: " . $callType);

                // $agentName = (strpos($callUniqueId, 'SOT') === 0)
                //     ? $request->input('parent_user_id')
                //     : $request->input('caller_username');

                // generateLogs("info", "\n Agent Name: " . $agentName);

                if (strpos($callUniqueId, 'SOT') === 0) {
                    // $data['caller_username'] = $agentName;
                    $data['caller_username'] = $request->input('caller_username');
                    $data['conf_state'] = $confState;

                    generateLogs("info", "\n Caller Username: " . $data['caller_username']);
                    generateLogs("info", "\n Conf State(2): " . $data['conf_state']);
                }
                // -------------
                // -------------
                $contactName = null;
                $mobile = $request->called_phone_number;

                if ($mobile) {
                    $contact = Contact::where('mobile', $mobile)->first(['first_name', 'last_name']);

                    if ($contact) {
                        $contactName = trim($contact->first_name . ' ' . $contact->last_name);
                    }
                }
                $data['called_contact_name'] = $contactName;
                // -------------
                // -------------

                // $agentName = $request->caller_username;
                // $agentId = Users::select('id')->where('username', $agentName)->first();

                // generateLogs("info", "\n Agent ID: " . json_encode($agentId));

                // if ($type !== 'call_initiated') {
                //     if ($agentId) {
                //         $liveAgent = LiveAgents::where('user_id', $agentId->id)
                //             ->where('extension', $data['caller_user_extension'])
                //             ->first();
                //         if ($liveAgent) {
                //             $liveAgent->bypassObserver = true;
                //             $liveAgent->status = $data['user_status'];
                //             $liveAgent->uniqueid = $data['call_unique_id'];
                //             $liveAgent->customer = $data['called_phone_number'];
                //             $liveAgent->call_date = $data['call_date'];
                //             $liveAgent->call_direction = 'out_call';
                //             $liveAgent->save();

                //             broadcast(new AgentLiveStatus($liveAgent, $agentId->id, $domain));
                //         }
                //     }
                // }

                if ($type !== 'call_initiated') {
                    foreach ($ids as $role => $userId) {
                        if (!$userId) continue;

                        $liveAgent = LiveAgents::where('user_id', $userId)
                            ->where('extension', $data['caller_user_extension'])
                            ->first();

                        if ($liveAgent) {
                            $liveAgent->bypassObserver = true;
                            $liveAgent->status = $data['user_status'];
                            $liveAgent->uniqueid = $data['call_unique_id'];
                            $liveAgent->customer = $data['called_phone_number'];
                            $liveAgent->call_date = $data['call_date'];
                            $liveAgent->call_direction = 'out_call';
                            $liveAgent->save();

                            broadcast(new AgentLiveStatus($liveAgent, $userId, $domain));
                        }
                    }
                }

                if ($type == 'call_initiated') {
                    if ($callType == 'outgoing') {
                        $activity = 'OutCall';
                    } 
                    if ($callType == 'incoming') {
                        $activity = 'InCall';
                    }

                    $latestActivity = getLatestUserActivity($agentUser?->id, $request->campaign_id);

                    if (!$latestActivity) {
                        return null;
                    }

                    // Calculate time difference from last activity
                    $timeDiff = calculateTimeDiff($latestActivity->created_at);
                    $durationField = $latestActivity->activity === 'Pause'
                    ? 'pause_duration'
                    : 'resume_duration';

                    generateLogs("info", "\n User_id: " . $agentUser?->id);
                    UserDailyActivity::create([
                        'campaign_id'    => $request->campaign_id,
                        'user_id'        => $agentUser?->id,
                        'call_unique_id' => $request->call_unique_id,
                        'activity'       => $activity,
                        $durationField   => $timeDiff,
                        
                    ]);
                }

                if ($type == 'call_hangup') {
                    $activityData = [
                        'campaign_id' => $request->campaign_id,
                        'user_id' => $agentUser?->id,
                        'call_unique_id' => $request->call_unique_id,
                        'activity' => 'Endcall',
                    ];

                    // $lastActivity = getLatestUserActivity($ids, $request->campaign_id);

                       $lastActivity = UserDailyActivity::where('user_id', $agentUser?->id)
                        ->where('campaign_id', $request->campaign_id)
                        ->whereIn('activity', ['InCall', 'OutCall'])
                        ->latest('id')
                        ->first();

                    if (!$lastActivity) {
                        return null;
                    }

                    if ($lastActivity) {
                        $activityData['call_duration'] = calculateTimeDiff($lastActivity->created_at);
                    }
        
                    UserDailyActivity::create($activityData);
                }

                // $callLog = $this->saveCallLogs($data, $agentId->id, $domain);
                $callLog = $this->saveCallLogs($data, $ids, $domain);

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $callLog,
                    trans('message.call_data_created')
                );
            }

            return $this->sendErrorResponse();
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Request : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    // Determine call type (incoming or outgoing)
    private function determineCallType(Request $request)
    {
        return $request->filled('inbound_group_id') ? 'incoming' : 'outgoing';
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
            case 'SO':
                return 'conference_call';
            default:
                return 'unknown';
        }
    }

    // Prepare the common data for call log
    private function prepareCallData(Request $request, string $type, string $callType)
    {
        $status = null;

        if ($type === 'call_connected') {
            $status = 'incall';
        } elseif ($type === 'call_hangup') {
            $status = 'disconnect';
        }

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
            'user_status' => $status,
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
            'cust_call_time' => Carbon::parse($request->cust_call_time)->format('Y-m-d H:i:s'),
            'aband_call_time' => $request->input('aband_call_time') ?? null,
            'call_did_pattern' => $request->input('call_did_pattern') ?? null,
            'type' => 'call_history',
            'call_type' => $callType,
            'json_data' => json_encode($request->all())
        ];
    }

    // Save the call data to both main and temporary logs
    // private function saveCallLogs(array $data, int $id, string $domain)
    private function saveCallLogs(array $data, array $ids, string $domain)
    {
        // Save to neox_call_logs table
        $callLog = $this->webhookCallRepository->saveCallLog($data);

        // Prepare and save to neox_temp_call_logs
        $tempData = array_merge($data, [
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString()
        ]);
        // $this->webhookCallRepository->saveTempCallLog($tempData, $id, $domain);
        $this->webhookCallRepository->saveTempCallLog($tempData, $ids, $domain);
        return $callLog;
    }

    // Handle sending error response
    private function sendErrorResponse()
    {
        return $this->apiResponse->getResponseStructure(
            trans('message.error'),
            null,
            trans('message.not_accessible')
        );
    }
}
