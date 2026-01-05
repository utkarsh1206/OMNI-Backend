<?php

namespace App\Services;

use App\Repositories\EcdialListRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ListResolverService
{
    protected $ecdialListRepository;

    public function __construct(EcdialListRepository $ecdialListRepository)
    {
        $this->ecdialListRepository = $ecdialListRepository;
    }

    /**
     * Resolves the list_id either from a direct parameter or from a campaign_id in the request.
     *
     * @param Request $request
     * @return array|JsonResponse
     * Returns ['list_id' => $id] on success, or a JsonResponse error on failure.
     */
    public function resolveListId(Request $request): array|JsonResponse
    {
        $resolvedListId = null;
        $errorResponse = null;

        $routeListId = $request->route('list_id', null);

        if ($routeListId) {
            generateLogs("info", "List ID from route: " . $routeListId);
            $resolvedListId = $routeListId;
        } else {
            $campaignId = $request->route('campaign_id', null);

            if ($campaignId) {
                generateLogs("info", "Campaign ID from route: " . $campaignId);

                $list = $this->ecdialListRepository
                    ->getListByCampaignId($campaignId);

                if ($list) {
                    generateLogs("info", "Resolved list from campaign: " . $list->list_id);
                    $resolvedListId = $list->list_id;
                } else {
                    $errorResponse = response()->json([
                        'error' => 'No list found for the given campaign ID'
                    ], 404);
                }
            }
        }

        if ($errorResponse) {
            return $errorResponse;
        }

        if (!$resolvedListId) {
            return response()->json([
                'error' => 'No list_id or campaign_id provided'
            ], 400);
        }

        return ['list_id' => $resolvedListId];
    }
}
