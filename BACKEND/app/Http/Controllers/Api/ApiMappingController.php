<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiMappingRequest;
use App\Repositories\ApiMappingRepository;
use Illuminate\Http\Request;

class ApiMappingController extends Controller
{
    private $repository;

    public function __construct(ApiMappingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all mappings
     */
    public function index()
    {
        return $this->repository->all();
    }

    /**
     * Create mapping(s)
     * Supports bulk and single insert
     */
    // public function store(Request $request)
    // {
    //     $payload = $request->all();

    //     // Bulk insert
    //     if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
    //         $data = $request->validate([
    //             '*.endpoint_id' => 'required|integer',
    //             '*.api_key' => 'required|string',
    //             '*.column_name' => 'required|string'
    //         ]);
    //         return $this->repository->create($data);
    //     }

    //     // Single insert
    //     $data = $request->validate([
    //         'endpoint_id' => 'required|integer',
    //         'api_key' => 'required|string',
    //         'column_name' => 'required|string'
    //     ]);

    //     return $this->repository->create($data);
    // }
    public function store(ApiMappingRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    /**
     * Get single mapping by ID
     */
    public function getById(int $id)
    {
        return $this->repository->getById($id);
    }

    /**
     * Get mappings for a specific endpoint
     */
    public function getByEndpointId(int $endpointId)
    {
        return $this->repository->getByEndpointId($endpointId);
    }

    /**
     * Get mappings for a specific list (via endpoints)
     */
    public function getByListId(int $listId)
    {
        return $this->repository->getByListId($listId);
    }

    /**
     * Update mapping
     */
    // public function update(Request $request, int $id)
    // {
    //     $data = $request->validate([
    //         'endpoint_id' => 'sometimes|integer',
    //         'api_key' => 'sometimes|string',
    //         'column_name' => 'sometimes|string'
    //     ]);

    //     return $this->repository->update($id, $data);
    // }
    public function update(ApiMappingRequest $request, int $id)
    {
        return $this->repository->update($id, $request->validated());
    }

    /**
     * Delete mapping
     */
    public function destroy(int $id)
    {
        return $this->repository->delete($id);
    }
}
