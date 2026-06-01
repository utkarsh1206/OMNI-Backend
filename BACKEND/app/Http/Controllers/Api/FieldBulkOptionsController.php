<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Response\CustomApiResponse;
use App\Services\FieldBulkOptionsService;
use App\Services\ListResolverService;
use Illuminate\Http\Request;

class FieldBulkOptionsController extends Controller
{
    public function __construct(
        protected FieldBulkOptionsService $bulkOptionsService,
        protected CustomApiResponse $apiResponse,
        protected ListResolverService $listResolverService
    ) {}

    /**
     * Fetch options for all independent dynamic list fields in a list.
     *
     * GET /api/dynamic-list-fields/bulk-options?list_id=<id>
     *
     * Returns a map of field_name => { mapped_options } for each independent
     * API-driven dynamic field. Fields with a dependency mapping are excluded.
     * Failed API calls return { error } instead of options.
     */
    public function dynamicBulkOptions(Request $request)
    {
        if (!config('omni.FIELD_API_ENABLED')) {
            return $this->apiResponse->getResponseStructure(null, [], 'Field API feature is not enabled.');
        }

        $resolved = $this->listResolverService->resolveListId($request);
        if ($resolved instanceof \Illuminate\Http\JsonResponse) {
            return $resolved;
        }

        $request->merge(['list_id' => (int) $resolved['list_id']]);
        $request->validate(['list_id' => ['required', 'integer', 'exists:ecdial_lists,list_id']]);

        $data = $this->bulkOptionsService->fetchForDynamic((int) $request->input('list_id'));

        return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
    }

    /**
     * Fetch options for all independent ecdial list fields in a list.
     *
     * GET /api/lists_fields/bulk-options?list_id=<id>
     *
     * Returns a map of field_name => { response } for each independent
     * API-driven ecdial field. Fields with a dependency mapping are excluded.
     * Failed API calls return { error } instead of response.
     */
    public function ecdialBulkOptions(Request $request)
    {
        if (!config('omni.FIELD_API_ENABLED')) {
            return $this->apiResponse->getResponseStructure(null, [], 'Field API feature is not enabled.');
        }

        $resolved = $this->listResolverService->resolveListId($request);
        if ($resolved instanceof \Illuminate\Http\JsonResponse) {
            return $resolved;
        }

        $request->merge(['list_id' => (int) $resolved['list_id']]);
        $request->validate(['list_id' => ['required', 'integer', 'exists:ecdial_lists,list_id']]);

        $data = $this->bulkOptionsService->fetchForEcdial((int) $request->input('list_id'));

        return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
    }
}
