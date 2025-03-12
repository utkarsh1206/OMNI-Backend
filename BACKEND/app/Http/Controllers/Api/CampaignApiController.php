<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CampaignApiRepository;

class CampaignApiController extends Controller
{
    protected $campaignRepository;

    public function __construct(CampaignApiRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function campaignList(Request $request)
    {
        $rules = [
            'user' => 'required|string',
        ];

        $validatedData = $request->validate($rules);

        $responseData = $this->campaignRepository->getCampaignList($validatedData);

        return $responseData;
    }
}
