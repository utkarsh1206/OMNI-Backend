<?php

namespace App\Repositories;

use App\Models\EcdialCampaign;
use App\Http\Response\CustomApiResponse;
use Exception;

class CampaignListRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $query = EcdialCampaign::query();
            $query->where('dial_method', '!=', 'VOICE_BLASTER');
            $campaigns = $query->orderBy('campaign_id')->get(['campaign_id', 'campaign_name']);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $campaigns, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating capgaign Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
