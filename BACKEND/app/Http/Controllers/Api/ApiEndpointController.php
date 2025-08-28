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
     * @OA\Get(
     *     path="/api/endpoints",
     *     summary="Get all API endpoints",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of API endpoints",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Endpoints retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="url", type="string"),
     *                     @OA\Property(property="method", type="string"),
     *                     @OA\Property(property="list_id", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return $this->repository->all();
    }

    /**
     * @OA\Post(
     *     path="/api/endpoints",
     *     summary="Create a new API endpoint",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name", "url", "method", "list_id", "mappings"},
     *             @OA\Property(property="name", type="string", example="User Endpoint"),
     *             @OA\Property(property="url", type="string", example="/users"),
     *             @OA\Property(property="method", type="string", example="GET"),
     *             @OA\Property(property="list_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="mappings",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="api_key", type="string", example="api_field"),
     *                     @OA\Property(property="column_name", type="string", example="db_column")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="API endpoint created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Endpoint and mappings created"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(ApiEndpointRequest $request)
    {
        return $this->repository->create($request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/endpoints/{id}",
     *     summary="Get API endpoint by ID",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the API endpoint",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endpoint found",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Endpoint not found"
     *     )
     * )
     */
    public function getById(int $id)
    {
        return $this->repository->getById($id);
    }

    /**
     * @OA\Get(
     *     path="/api/lists/{listId}/endpoints",
     *     summary="Get API endpoints by list ID",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="listId",
     *         in="path",
     *         description="List ID to filter endpoints",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endpoints for the list",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function getByListId(int $listId)
    {
        return $this->repository->getByListId($listId);
    }

    /**
     * @OA\Put(
     *     path="/api/endpoints/{id}",
     *     summary="Update an API endpoint",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the endpoint to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Updated Endpoint"),
     *             @OA\Property(property="url", type="string", example="/users/updated"),
     *             @OA\Property(property="method", type="string", example="POST"),
     *             @OA\Property(property="list_id", type="integer", example=2),
     *             @OA\Property(
     *                 property="mappings",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="source", type="string", example="api_field"),
     *                     @OA\Property(property="target", type="string", example="db_column")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endpoint updated",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Endpoint not found"
     *     )
     * )
     */
    public function update(ApiEndpointRequest $request, int $id)
    {
        return $this->repository->update($id, $request->validated());
    }

    /**
     * @OA\Delete(
     *     path="/api/endpoints/{id}",
     *     summary="Delete an API endpoint",
     *     tags={"API Endpoints"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the endpoint to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Endpoint deleted",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Endpoint not found"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        return $this->repository->delete($id);
    }
}
