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

    public function index()
    {
        try {
            $liveAgents = LiveAgents::where('user_id', Auth::user()->id)->first();

            if ($liveAgents) {
                $liveAgents->agent_last_activity = Carbon::now();
                $liveAgents->save();
                return response()->noContent();
            } else {
                
            }

        } catch (Exception $e) {
            generateLogs('error', 'Error in generating network-check : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
