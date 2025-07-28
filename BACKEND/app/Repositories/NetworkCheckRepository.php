<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
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

    // public function index($request)
    // {
    //     try {
    //         $liveAgents = LiveAgents::findOrFail($request->user_id);

    //         if ($liveAgents) {
    //             $liveAgents->status_last_updated_at = Carbon::now();
    //             $liveAgents->save();
    //             return response()->noContent();
    //         }
    //     } catch (Exception $e) {
    //         generateLogs('error', 'Error in generating network-check : ', $e->getMessage());
    //         return $this->apiResponse->handleAndResponseException($e);
    //     }
    // }

    public function index(LiveAgents $liveAgent)
    {
        try {
            $liveAgent->status_last_updated_at = Carbon::now();
            $liveAgent->save();

            return response()->noContent();
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating network-check : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
