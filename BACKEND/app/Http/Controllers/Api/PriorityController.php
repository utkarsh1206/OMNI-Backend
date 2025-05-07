<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriorityRequest;
use App\Models\Priority;
use App\Repositories\PriorityRepository;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    private $priorityRepository;

    public function __construct(PriorityRepository $priorityRepository)
    {
        $this->priorityRepository = $priorityRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/priorities",
     *     summary="Get all priorities",
     *     description="Retrieve a list of priorities.",
     *     tags={"Priorities"},
     *     security={{"bearerAuth":{}}},
     *     operationId="index",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for priorities",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter priorities",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of priorities",
     *         @OA\JsonContent(
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching pause codes"
     *     )
     * )
     */

    public function index(Request $request)
    {
        return $this->priorityRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/priorities",
     *     summary="Create a new priority",
     *     description="Store a new priority.",
     *     operationId="storePriority",
     *     tags={"Priorities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="High"),
     *             @OA\Property(property="description", type="string", example="Tasks that need immediate attention"),
     *             @OA\Property(property="status", type="string", example="active"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Priority created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Priority created successfully."),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *         )
     *     )
     * )
     */

    public function store(PriorityRequest $request)
    {
        return $this->priorityRepository->store($request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/priorities/{id}",
     *     summary="Get a priority by ID",
     *     description="Retrieve details of a specific priority.",
     *     operationId="showPriority",
     *     tags={"Priorities"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Priority ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Priority details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully!"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Priority not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Priority not found")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        return $this->priorityRepository->show($id);
    }

    /**
     * @OA\Put(
     *     path="/api/priorities/{id}",
     *     summary="Update a priority",
     *     description="Update an existing priority by ID.",
     *     operationId="updatePriority",
     *     tags={"Priorities"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Priority ID",
     *         @OA\Schema(type="integer", example=1),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Medium"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Priority updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question updated successfully!"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Priority not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Priority not found")
     *         )
     *     )
     * )
     */

    public function update(PriorityRequest $request, Priority $priority)
    {
        return $this->priorityRepository->update($request->validated(), $priority);
    }

    /**
     * @OA\Delete(
     *     path="/api/priorities/{id}",
     *     summary="Delete a priority",
     *     description="Delete a priority by ID.",
     *     operationId="deletePriority",
     *     tags={"Priorities"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Priority ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Priority deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Priority not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Priority not found")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        return $this->priorityRepository->delete($id);
    }
}
