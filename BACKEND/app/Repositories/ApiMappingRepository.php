<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\ApiMapping;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiMappingRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function create(array $data)
    {
        try {
            // Bulk insert
            if (isset($data[0]) && is_array($data[0])) {
                $createdMappings = DB::transaction(function () use ($data) {
                    $created = [];
                    foreach ($data as $item) {
                        $created[] = ApiMapping::create($item);
                    }
                    return $created;
                });

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $createdMappings,
                    trans('message.mappings_created')
                );
            }

            // Single insert
            $mapping = ApiMapping::create($data);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $mapping,
                trans('message.mapping_created')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in creating Mapping: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(int $id, array $data)
    {
        try {
            $mapping = ApiMapping::find($id);
            if (!$mapping) {
                return response()->json(['message' => trans('message.mapping_not_found')], Response::HTTP_NOT_FOUND);
            }

            $mapping->update($data);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $mapping,
                trans('message.mapping_updated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in updating Mapping: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete(int $id)
    {
        try {
            $mapping = ApiMapping::find($id);
            if (!$mapping) {
                return response()->json(['message' => trans('message.mapping_not_found')], Response::HTTP_NOT_FOUND);
            }

            $mapping->delete();

            return $this->apiResponse->getResponseStructure(
                'success',
                null,
                trans('message.mapping_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in deleting Mapping: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getByListId(int $listId)
    {
        try {
            $mappings = ApiMapping::whereHas('endpoint', function ($q) use ($listId) {
                $q->where('list_id', $listId);
            })->get();

            if ($mappings->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    false,
                    null,
                    trans('message.no_mappings_found_for_list')
                );
            }

            return $this->apiResponse->getResponseStructure(
                'success',
                $mappings,
                trans('message.mappings_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting mappings by list ID: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function all()
    {
        try {
            $mappings = ApiMapping::all();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $mappings,
                trans('message.all_mappings_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting all mappings: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getByEndpointId(int $endpointId)
    {
        try {
            $mappings = ApiMapping::where('endpoint_id', $endpointId)->get();

            if ($mappings->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    'error',
                    null,
                    trans('message.no_mappings_found_for_endpoint')
                );
            }

            return $this->apiResponse->getResponseStructure(
                'success',
                $mappings,
                trans('message.mappings_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting mappings by endpoint ID: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getById(int $id)
    {
        try {
            $mapping = ApiMapping::find($id);

            if (!$mapping) {
                return response()->json(['message' => trans('message.mapping_not_found')], Response::HTTP_NOT_FOUND);
            }

            return $this->apiResponse->getResponseStructure(
                'success',
                $mapping,
                trans('message.mapping_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting mapping by ID: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
