<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\NeoxConfigurationRequest;
use App\Repositories\NeoxConfigurationRepository;
use App\Http\Resources\NeoxConfigurationResource;
use App\Models\NeoxConfiguration;

class NeoxConfigurationController extends Controller
{
    protected $neoxConfigurationRepository;

    public function __construct(NeoxConfigurationRepository $neoxConfigurationRepository)
    {
        $this->neoxConfigurationRepository = $neoxConfigurationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/neox-configurations",
     *     summary="List Neox Configurations",
     *     description="Fetch a list of Neox configurations with optional search and pagination.",
     *     operationId="listNeoxConfigurations",
     *     tags={"Neox Configuration"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for Neox token or local IP",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Neox configurations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Invalid pagination parameters")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->neoxConfigurationRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/neox-configurations",
     *     summary="Create a Neox Configuration",
     *     description="Create a new Neox configuration with provided token and local IP.",
     *     operationId="createNeoxConfiguration",
     *     tags={"Neox Configuration"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"neox_token", "neox_local_ip"},
     *             @OA\Property(property="neox_token", type="string", example="ABC123"),
     *             @OA\Property(property="neox_local_ip", type="string", example="192.168.1.1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Neox configuration created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data or duplicate configuration",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Neox token already exists")
     *         )
     *     )
     * )
     */
    public function store(NeoxConfigurationRequest $request)
    {
        return $this->neoxConfigurationRepository->create($request);
    }

    /**
     * @OA\Get(
     *     path="/api/neox-configurations/{id}",
     *     summary="Get a Neox Configuration by ID",
     *     description="Fetch the details of a Neox configuration by its unique ID.",
     *     operationId="showNeoxConfiguration",
     *     tags={"Neox Configuration"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Neox configuration to retrieve",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched Neox configuration",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Neox configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Neox configuration not found")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        $list = NeoxConfiguration::findOrFail($id);
        return new NeoxConfigurationResource($list);
    }

    /**
     * @OA\Put(
     *     path="/api/neox-configurations/{id}",
     *     summary="Update a Neox Configuration",
     *     description="Update an existing Neox configuration based on the provided ID.",
     *     operationId="updateNeoxConfiguration",
     *     tags={"Neox Configuration"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Neox configuration to update",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="neox_token", type="string", example="XYZ123"),
     *             @OA\Property(property="neox_local_ip", type="string", example="192.168.1.2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Neox configuration updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data or configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Neox token already exists")
     *         )
     *     )
     * )
     */
    public function update(NeoxConfigurationRequest $request, $id)
    {
        return $this->neoxConfigurationRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/neox-configurations/{id}",
     *     summary="Delete a Neox Configuration",
     *     description="Delete a Neox configuration by its ID.",
     *     operationId="deleteNeoxConfiguration",
     *     tags={"Neox Configuration"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the Neox configuration to delete",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Neox configuration deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Neox configuration not found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        return $this->neoxConfigurationRepository->delete($id);
    }
}
