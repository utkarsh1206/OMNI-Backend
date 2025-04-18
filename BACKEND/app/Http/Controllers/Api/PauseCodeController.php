<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PauseCodeRequest;
use Illuminate\Http\Request;
use App\Repositories\PauseCodeRepository;

class PauseCodeController extends Controller
{
    private $pauseCodeRepository;

    public function __construct(PauseCodeRepository $pauseCodeRepository)
    {
        $this->pauseCodeRepository = $pauseCodeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/pauseCode",
     *     summary="Get all pause codes",
     *     description="Fetches pause codes with optional search and pagination",
     *     tags={"Pause Codes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for pause code names or codes",
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
     *         name="code",
     *         in="query",
     *         description="Filter pause codes by code",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter pause codes by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of pause codes",
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
        return $this->pauseCodeRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/pauseCode",
     *     summary="Create a new pause code",
     *     description="Creates a new pause code with the provided data.",
     *     tags={"Pause Codes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"code", "name"},
     *                 @OA\Property(property="code", type="string", example="PC001", description="Unique code for the pause code"),
     *                 @OA\Property(property="name", type="string", example="Break Time", description="Name of the pause code"),
     *                 @OA\Property(property="description", type="string", example="Used for regular break periods", description="Optional description of the pause code")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pause code created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code created successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="code", type="string", example="PC001"),
     *                 @OA\Property(property="name", type="string", example="Break Time"),
     *                 @OA\Property(property="description", type="string", example="Used for regular break periods")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating pause code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating pause code: SQL error")
     *         )
     *     )
     * )
     */
    public function store(PauseCodeRequest $request)
    {
        return $this->pauseCodeRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/pauseCode/{id}",
     *     summary="Get a pause code by ID",
     *     description="Fetches a specific pause code by its ID.",
     *     tags={"Pause Codes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pause code to fetch",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pause code details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="PC001"),
     *                 @OA\Property(property="name", type="string", example="Break Time"),
     *                 @OA\Property(property="description", type="string", example="Used for regular break periods")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pause code not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching pause code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching pause code: SQL error")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        return $this->pauseCodeRepository->show($id);
    }

    /**
     * @OA\Put(
     *     path="/api/pauseCode/{id}",
     *     summary="Update an existing pause code",
     *     description="Updates the details of an existing pause code based on the provided data.",
     *     tags={"Pause Codes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pause code to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"code", "name"},
     *                 @OA\Property(property="code", type="string", example="PC002", description="Updated code for the pause code"),
     *                 @OA\Property(property="name", type="string", example="Updated Pause Code Name", description="Updated name of the pause code"),
     *                 @OA\Property(property="description", type="string", example="Updated pause code description", description="Optional updated description of the pause code")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pause code updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="PC002"),
     *                 @OA\Property(property="name", type="string", example="Updated Pause Code Name"),
     *                 @OA\Property(property="description", type="string", example="Updated pause code description")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pause code not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating pause code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating pause code: SQL error")
     *         )
     *     )
     * )
     */
    public function update(PauseCodeRequest $request, $id)
    {
        return $this->pauseCodeRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/pauseCode/{id}",
     *     summary="Delete a pause code by ID",
     *     description="Deletes an existing pause code by its ID.",
     *     tags={"Pause Codes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pause code to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pause code deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pause code not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pause code not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting pause code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting pause code: SQL error")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        return $this->pauseCodeRepository->delete($id);
    }
}