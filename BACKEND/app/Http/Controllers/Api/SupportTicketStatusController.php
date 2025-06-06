<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketStatusRequest;
use App\Models\SupportTicketStatus;
use App\Repositories\SupportTicketStatusRepository;
use Illuminate\Http\Request;

class SupportTicketStatusController extends Controller
{
    private $statusRepository;

    public function __construct(SupportTicketStatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
    }


    /**
     * @OA\Get(
     *     path="/api/ticket-status",
     *     summary="Get all status",
     *     description="Retrieve a list of status.",
     *     tags={"Suppot Ticket Status"},
     *     security={{"bearerAuth":{}}},
     *     operationId="listStatus",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for status",
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
     *         description="Filter status",
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
     *         description="A list of status",
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
        return $this->statusRepository->list($request);
    }

   /**
     * @OA\Post(
     *     path="/api/ticket-status",
     *     summary="Create a new status",
     *     description="Store a new status.",
     *     operationId="storeStatus",
     *     tags={"Suppot Ticket Status"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Inprogress"),
     *             @OA\Property(property="description", type="string", example="Tasks that need be in progress"),
     *             @OA\Property(property="status", type="string", example="active"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="status created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="status created successfully."),
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

    public function store(SupportTicketStatusRequest $request)
    {
        return $this->statusRepository->store($request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/ticket-status/{id}",
     *     summary="Get a status by ID",
     *     description="Retrieve details of a specific status.",
     *     operationId="showStatus",
     *     tags={"Suppot Ticket Status"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="status ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="status details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully!"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="status not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="status not found")
     *         )
     *     )
     * )
     */

    public function show($id)
    {
        return $this->statusRepository->show($id);
    }

   /**
     * @OA\Put(
     *     path="/api/ticket-status/{id}",
     *     summary="Update a status",
     *     description="Update an existing status by ID.",
     *     operationId="updateStatus",
     *     tags={"Suppot Ticket Status"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="status ID",
     *         @OA\Schema(type="integer", example=1),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Waiting for customer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="status updated successfully!"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="status not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="status not found")
     *         )
     *     )
     * )
     */

    public function update(SupportTicketStatusRequest $request, SupportTicketStatus $ticket_status)
    {
        return $this->statusRepository->update($request->validated(), $ticket_status);
    }

     /**
     * @OA\Delete(
     *     path="/api/ticket-status/{id}",
     *     summary="Delete a status",
     *     description="Delete a status by ID.",
     *     operationId="deleteStatus",
     *     tags={"Suppot Ticket Status"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="status ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="status deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="status deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="status not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="status not found")
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        return $this->statusRepository->delete($id);
    }
}
