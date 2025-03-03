<?php

namespace App\Repositories;
use App\Models\EcdialCampaign;
use App\Http\Response\CustomApiResponse;


class CampaignListRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {     
        $query = EcdialCampaign::query();
        $query->where('dial_method', '!=', 'VOICE_BLASTER');
        $campaigns = $query->orderBy('campaign_id')->get(['campaign_id', 'campaign_name']);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $campaigns, trans('message.data_fetch'));
    }

   

}
