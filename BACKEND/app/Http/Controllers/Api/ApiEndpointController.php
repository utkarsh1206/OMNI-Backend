<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiEndpointRequest;
use App\Repositories\ApiEndpointRepository;

class ApiEndpointController extends Controller
{
    private $repository;

    public function __construct(ApiEndpointRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all endpoints
     */
    public function index()
    {
        return $this->repository->all();
    }

    /**
     * Create new endpoint
     */
    public function store(ApiEndpointRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    /**
     * Get single endpoint by ID
     */
    public function getById(int $id)
    {
        return $this->repository->getById($id);
    }

    /**
     * Get all endpoints for a list
     */
    public function getByListId(int $listId)
    {
        return $this->repository->getByListId($listId);
    }

    /**
     * Update endpoint
     */
    public function update(ApiEndpointRequest $request, int $id)
    {
        return $this->repository->update($id, $request->validated());
    }

    /**
     * Delete endpoint
     */
    public function destroy(int $id)
    {
        return $this->repository->delete($id);
    }
}
