<?php

namespace App\Repositories;

use App\Models\NeoxConfiguration;
use Illuminate\Http\Request;
use App\Http\Resources\NeoxConfigurationResource;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;

class NeoxConfigurationRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = NeoxConfiguration::query();

            // Search Filter
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('neox_token', 'like', "%$searchTerm%")
                        ->orWhere('neox_local_ip', 'like', "%$searchTerm%");
                });
            }

            if ($page === 'all') {
                $listData = $query->get();
                return $this->apiResponse->getResponseStructure(trans('message.success'), $listData, trans('message.data_fetch'));
            } else {
                $listData = $query->paginate($perPage);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $listData, trans('message.data_fetch'));
            }
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function create(Request $request)
    {
        try {
            if (NeoxConfiguration::where('neox_token', $request->neox_token)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.neox_token_exists'));
            }

            if (NeoxConfiguration::where('neox_local_ip', $request->neox_local_ip)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.neox_local_ip_exists'));
            }

            $configuration = NeoxConfiguration::create([
                'neox_token' => $request->neox_token,
                'neox_local_ip' => $request->neox_local_ip,
            ]);

            $data = new NeoxConfigurationResource($configuration);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.configuration_create_success'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $configuration = NeoxConfiguration::findOrFail($id);

            if (NeoxConfiguration::where('neox_token', $request->neox_token)->where('id', '!=', $id)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.neox_token_exists'));
            }

            if (NeoxConfiguration::where('neox_local_ip', $request->neox_local_ip)->where('id', '!=', $id)->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.neox_local_ip_exists'));
            }

            $configuration->update([
                'neox_token' => $request->neox_token ?? $configuration->neox_token,
                'neox_local_ip' => $request->neox_local_ip ?? $configuration->neox_local_ip,
            ]);

            $data = new NeoxConfigurationResource($configuration);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.configuration_update_success'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $configuration = NeoxConfiguration::findOrFail($id);
            $configuration->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $id, trans('message.configuration_delete_success'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
