<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcdialCampaign;
use Illuminate\Http\Request;

class CampaignListController extends Controller
{
    public function index(Request $request)
    {
        $acl_campaign_qstring = '';
        
        $query = EcdialCampaign::query();

        if ($acl_campaign_qstring) {
            $query->whereRaw($acl_campaign_qstring);
        }

        $query->where('dial_method', '!=', 'VOICE_BLASTER');

        $campaigns = $query->orderBy('campaign_id')->get(['campaign_id', 'campaign_name']);

        // Return the response
        return response()->json($campaigns);
    }
}
