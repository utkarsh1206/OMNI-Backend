<?php

namespace App\Services;

use App\Repositories\EcdialListRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

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

        // Priority 1: campaign from bearer token for role_id 3.
        $token = $request->bearerToken();

        if ($token) {
            try {
                $payload = extractPayload($token);

                if ($payload && isset($payload['campaign'], $payload['role_id']) && (int) $payload['role_id'] === 3) {
                    $list = $this->ecdialListRepository->getListByCampaignId($payload['campaign']);

                    if (!$list) {
                        return response()->json([
                            'error' => 'No list found for the given campaign ID'
                        ], 404);
                    }

                    generateLogs('info', 'Resolved list from payload campaign: ' . $list->list_id);
                    $resolvedListId = $list->list_id;
                }
            } catch (Throwable $th) {
                generateLogs('warning', 'Failed to resolve list from bearer payload: ' . $th->getMessage());
            }
        }

        // Priority 2: explicit request list_id or route list_id.
        if (is_null($resolvedListId)) {
            $requestListId = $request->input('list_id', null);
            $routeListId = $request->route('list_id', null);
            $resolvedListId = $requestListId ?: $routeListId;

            if ($resolvedListId) {
                generateLogs('info', 'Resolved list from request/route list_id: ' . $resolvedListId);
            }
        }

        // Priority 3: campaign_id from request/route fallback.
        if (is_null($resolvedListId)) {
            $campaignId = $request->input('campaign_id', $request->route('campaign_id', null));

            if ($campaignId) {
                generateLogs('info', 'Campaign ID from request/route: ' . $campaignId);

                $list = $this->ecdialListRepository->getListByCampaignId($campaignId);

                if (!$list) {
                    return response()->json([
                        'error' => 'No list found for the given campaign ID'
                    ], 404);
                }

                generateLogs('info', 'Resolved list from campaign: ' . $list->list_id);
                $resolvedListId = $list->list_id;
            }
        }

        if (is_null($resolvedListId)) {
            return response()->json([
                'error' => 'No list_id or campaign_id provided'
            ], 400);
        }

        return ['list_id' => (int) $resolvedListId];
    }
}
