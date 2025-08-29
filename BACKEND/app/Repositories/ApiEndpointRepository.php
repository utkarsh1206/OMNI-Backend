<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\ApiEndpoint;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiEndpointRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function create(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Extract mappings data
                $mappingsData = $data['mappings'];
                unset($data['mappings']);

                // 2. Create the Endpoint
                $endpoint = ApiEndpoint::create($data);

                // 3. Create the Mappings
                $mappings = [];
                foreach ($mappingsData as $mappingData) {
                    $mappings[] = $endpoint->mappings()->create($mappingData);
                }

                // 4. Return success response with both endpoint and mappings
                $response_data = [
                    'endpoint' => $endpoint,
                    'mappings' => $mappings
                ];

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $response_data,
                    trans('message.endpoint_and_mappings_created')
                );
            });
        } catch (Exception $e) {
            generateLogs('error', 'Error in creating Endpoint and Mappings: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $endpoint = ApiEndpoint::find($id);

                if (!$endpoint) {
                    return response()->json(['message' => trans('message.endpoint_not_found')], Response::HTTP_NOT_FOUND);
                }

                // Separate endpoint data and mappings data
                $mappingsData = $data['mappings'] ?? [];
                unset($data['mappings']);

                // 1. Update the endpoint's simple attributes
                $endpoint->update($data);

                // 2. Sync the mappings
                $existingMappingIds = $endpoint->mappings->pluck('id')->toArray();
                $incomingMappingIds = [];

                $mappingsToUpdateOrCreate = [];
                foreach ($mappingsData as $mapping) {
                    // Check if the mapping has an ID (i.e., it's an existing mapping)
                    if (isset($mapping['id'])) {
                        $incomingMappingIds[] = $mapping['id'];
                        // Mark for update
                        $mappingsToUpdateOrCreate[] = $mapping;
                    } else {
                        // Mark for creation (it's a new mapping)
                        $mappingsToUpdateOrCreate[] = $mapping;
                    }
                }

                // Mappings to delete are those that exist in the database but are not in the incoming payload
                $mappingsToDelete = array_diff($existingMappingIds, $incomingMappingIds);
                if (!empty($mappingsToDelete)) {
                    $endpoint->mappings()->whereIn('id', $mappingsToDelete)->delete();
                }

                // Update or create mappings
                foreach ($mappingsToUpdateOrCreate as $mapping) {
                    if (isset($mapping['id'])) {
                        // Update existing mapping
                        $endpoint->mappings()->where('id', $mapping['id'])->update($mapping);
                    } else {
                        // Create new mapping
                        $endpoint->mappings()->create($mapping);
                    }
                }

                // 3. Retrieve the updated endpoint with all its mappings
                $endpoint->load('mappings');

                // 4. Prepare the response data
                $responseData = [
                    'endpoint' => $endpoint,
                    'mappings' => $endpoint->mappings,
                ];

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $responseData,
                    trans('message.endpoint_updated')
                );
            });
        } catch (Exception $e) {
            generateLogs('error', 'Error in updating Endpoint: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete(int $id)
    {
        try {
            $endpoint = ApiEndpoint::find($id);
            if (!$endpoint) {
                return response()->json(['message' => trans('message.endpoint_not_found')], Response::HTTP_NOT_FOUND);
            }

            $endpoint->delete();

            return $this->apiResponse->getResponseStructure(
                'success',
                null,
                'Endpoint deleted successfully.' // Or trans('message.endpoint_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in deleting Endpoint: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getByListId(int $listId)
    {
        try {
            $endpoints = ApiEndpoint::where('list_id', $listId)->get();

            if ($endpoints->isEmpty()) {
                return $this->apiResponse->getResponseStructure(
                    false,
                    null,
                    trans('message.no_endpoints_found_for_list')
                );
            }

            return $this->apiResponse->getResponseStructure(
                'success',
                $endpoints,
                trans('message.endpoints_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting endpoints by list ID: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function all()
    {
        try {
            $endpoints = ApiEndpoint::all();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $endpoints,
                trans('message.endpoints_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting all endpoints: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getById(int $id)
    {
        try {
            $endpoint = ApiEndpoint::find($id);
            if (!$endpoint) {
                return response()->json(['message' => trans('message.endpoint_not_found')], Response::HTTP_NOT_FOUND);
            }

            return $this->apiResponse->getResponseStructure(
                'success',
                $endpoint,
                trans('message.endpoint_retrieved')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in getting endpoint by ID: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
