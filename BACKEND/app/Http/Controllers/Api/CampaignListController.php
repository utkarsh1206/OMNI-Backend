<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\CampaignListRepository;

class CampaignListController extends Controller
{
    private $campaignListRepository;

    public function __construct(CampaignListRepository $campaignListRepository)
    {
        $this->campaignListRepository = $campaignListRepository;
    }
    /**
     * @OA\Get(
     *     path="/api/campaigns",
     *     summary="Get a list of campaigns",
     *     description="Fetches a list of campaigns excluding those with a 'VOICE_BLASTER' dial method.",
     *     operationId="getCampaigns",
     *     tags={"Campaigns"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="campaign_id", type="integer", example=101),
     *                 @OA\Property(property="campaign_name", type="string", example="Sales Campaign")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function index()
    {
        return $this->campaignListRepository->list();
    }
}
