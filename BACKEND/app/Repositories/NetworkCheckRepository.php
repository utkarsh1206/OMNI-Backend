<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\LiveAgents;
use Carbon\Carbon;
use Exception;

class NetworkCheckRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index($request)
    {
        try {
            $authorizationHeader = $request->bearerToken();
            $payload = extractPayload($authorizationHeader);

            if ($payload['role_id'] == 0) {
                return response()->noContent();
            }

            $liveAgents = LiveAgents::where('user_id', Auth::user()->id)->first();

            if ($liveAgents) {
                $liveAgents->agent_last_activity = Carbon::now();
                $liveAgents->save();
                return response()->noContent();
            } else {
                // Do Nothing
            }

        } catch (Exception $e) {
            generateLogs('error', 'Error in generating network-check : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
