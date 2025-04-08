<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\CustomLeadRepository;

class CustomLeadController extends Controller
{
    protected $leadRepository;

    public function __construct(CustomLeadRepository $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * @OA\Get(
     *      path="/api/custom-leads/{list_id}",
     *      tags={"Custom Leads"},
     *      summary="Get list of leads",
     *      description="Retrieve a paginated list of leads for a given list ID.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="list_id",
     *          in="path",
     *          required=true,
     *          description="List ID to fetch leads",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Number of leads per page",
     *          @OA\Schema(type="integer", default=10)
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="Page number or 'all' to fetch all leads",
     *          @OA\Schema(type="string", default="1")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(type="object")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="List not found"
     *      )
     * )
     */
    public function index(Request $request, $list_id)
    {
        return $this->leadRepository->getLeadById($request, $list_id);
    }

    /**
     * @OA\Get(
     *      path="/api/custom-leads/{list_id}/{lead_id}",
     *      tags={"Custom Leads"},
     *      summary="Get a single lead",
     *      description="Fetch details of a specific lead by ID",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="list_id",
     *          in="path",
     *          required=true,
     *          description="List ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="Lead ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(type="object")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found"
     *      )
     * )
     */
    public function show(Request $request, $list_id, $lead_id = null)
    {
        return $this->leadRepository->getLeadById($request, $list_id, $lead_id);
    }

    /**
     * @OA\Put(
     *      path="/api/custom-leads/{list_id}/{lead_id}",
     *      tags={"Custom Leads"},
     *      summary="Update a lead",
     *      description="Update a specific lead's details",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="list_id",
     *          in="path",
     *          required=true,
     *          description="List ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="Lead ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Lead data to update",
     *          @OA\JsonContent(
     *              type="object",
     *              example={"name": "John Doe", "email": "john@example.com"}
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead updated successfully",
     *          @OA\JsonContent(type="object")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid request data"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found"
     *      )
     * )
     */
    public function update(Request $request, $list_id, $lead_id)
    {
        $table = "custom_lead_" . $list_id;

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Get table columns dynamically
        $columns = DB::getSchemaBuilder()->getColumnListing($table);

        // Remove primary key (assuming 'id' is the primary key)
        $columns = array_filter($columns, fn($column) => $column !== 'id');

        // Only keep fields that exist in the table
        $updateData = $request->only($columns);

        // Ensure there's data to update
        if (empty($updateData)) {
            return response()->json(['error' => 'No valid fields provided for update'], 400);
        }

        return $this->leadRepository->updateLead($list_id, $lead_id, $updateData);
    }

    /**
     * @OA\Delete(
     *      path="/api/custom-leads/{list_id}/{lead_id}",
     *      tags={"Custom Leads"},
     *      summary="Delete a lead",
     *      description="Delete a specific lead from the system",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="list_id",
     *          in="path",
     *          required=true,
     *          description="List ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="Lead ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead deleted successfully",
     *          @OA\JsonContent(type="object")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found"
     *      )
     * )
     */
    public function destroy($list_id, $lead_id)
    {
        return $this->leadRepository->deleteLead($list_id, $lead_id);
    }
}
