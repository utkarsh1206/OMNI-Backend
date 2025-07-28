<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NetworkCheckRequest;
use App\Repositories\NetworkCheckRepository;
use App\Models\LiveAgents;

class NetworkCheckController extends Controller
{
    protected $networkCheckRepository;

    public function __construct(NetworkCheckRepository $networkCheckRepository)
    {
        $this->networkCheckRepository = $networkCheckRepository;
    }

    // public function index(NetworkCheckRequest $request)
    // {
    //     return $this->networkCheckRepository->index($request);
    // }

    public function index(LiveAgents $live_agent)
    {
        return $this->networkCheckRepository->index($live_agent);
    }
}
