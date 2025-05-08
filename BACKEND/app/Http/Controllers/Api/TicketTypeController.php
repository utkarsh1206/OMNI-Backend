<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketTypeRequest;
use App\Models\TicketType;
use App\Repositories\TicketTypeRepository;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    private $ticketTypeRepository;

    public function __construct(TicketTypeRepository $ticketTypeRepository)
    {
        $this->ticketTypeRepository = $ticketTypeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/ticket-types",
     *     summary="Get all ticket types",
     *     description="Retrieve a list of ticket types with pagination and filtering options.",
     *     tags={"Ticket Types"},
     *     security={{"bearerAuth":{}}},
     *     operationId="ticketTypesIndex",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for ticket types",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by ticket type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (active/inactive)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
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
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function index(Request $request)
    {
        return $this->ticketTypeRepository->list($request);
    }


    /**
     * @OA\Post(
     *     path="/api/ticket-types",
     *     summary="Create a new ticket type",
     *     description="Create a new ticket type with the provided details.",
     *     tags={"Ticket Types"},
     *     security={{"bearerAuth":{}}},
     *     operationId="ticketTypesStore",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", example="General Inquiry"),
     *             @OA\Property(property="description", type="string", example="General customer questions"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ticket type created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function store(TicketTypeRequest $request)
    {
        return $this->ticketTypeRepository->store($request->validated());
    }

    /**
     * @OA\Get(
     *     path="/api/ticket-types/{id}",
     *     summary="Get a ticket type by ID",
     *     description="Retrieve details of a specific ticket type.",
     *     tags={"Ticket Types"},
     *     security={{"bearerAuth":{}}},
     *     operationId="ticketTypesShow",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ticket type ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket type not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function show($id)
    {
        return $this->ticketTypeRepository->show($id);
    }

    /**
     * @OA\Put(
     *     path="/api/ticket-types/{id}",
     *     summary="Update a ticket type",
     *     description="Update an existing ticket type with the provided details.",
     *     tags={"Ticket Types"},
     *     security={{"bearerAuth":{}}},
     *     operationId="ticketTypesUpdate",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ticket type ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="Technical Support", maxLength=255),
     *             @OA\Property(property="description", type="string", example="Technical issues and support", nullable=true),
     *             @OA\Property(property="status", type="string", example="active", enum={"active", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket type updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket type not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function update(TicketTypeRequest $request, TicketType $ticketType)
    {
        return $this->ticketTypeRepository->update($request->validated(), $ticketType);
    }


    /**
     * @OA\Delete(
     *     path="/api/ticket-types/{id}",
     *     summary="Delete a ticket type",
     *     description="Delete a specific ticket type by ID.",
     *     tags={"Ticket Types"},
     *     security={{"bearerAuth":{}}},
     *     operationId="ticketTypesDelete",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Ticket type ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket type deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket type not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function destroy(string $id)
    {
        return $this->ticketTypeRepository->delete($id);
    }
}
