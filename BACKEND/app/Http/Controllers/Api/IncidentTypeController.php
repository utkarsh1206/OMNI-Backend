<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncidentTypeRequest;
use Illuminate\Http\Request;
use App\Models\IncidentType;
use App\Repositories\IncidentTypeRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class IncidentTypeController extends Controller
{

    private $incidentTypeRepository;

    public function __construct(IncidentTypeRepository $incidentTypeRepository)
    {
        $this->incidentTypeRepository = $incidentTypeRepository;
    }


    /**
     * @OA\Get(
     *     path="/incident-types",
     *     summary="Get a list of incident types",
     *     description="Retrieve a list of incident types with optional filters such as search, name, priority, department, category, and SLA.",
     *     operationId="getIncidentTypes",
     *     tags={"IncidentTypes"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for incident type names.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by incident type name.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority level.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="department_id",
     *         in="query",
     *         description="Filter by department ID.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sla_id",
     *         in="query",
     *         description="Filter by SLA ID.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination, or 'all' to get all records.",
     *         required=false,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of incident types",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     properties={
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="priority", type="string"),
     *                         @OA\Property(property="category_id", type="integer"),
     *                         @OA\Property(property="sla_id", type="integer")
     *                     }
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching incident types",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
       return $this->incidentTypeRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/incident-types",
     *     summary="Create a new incident type",
     *     description="Create a new incident type with provided data such as name, description, category, priority, and SLA.",
     *     operationId="storeIncidentType",
     *     tags={"IncidentTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data required to create an incident type",
     *         @OA\JsonContent(
     *             required={"name", "description", "category_id", "priority", "sla_id"},
     *             @OA\Property(property="name", type="string", description="Name of the incident type"),
     *             @OA\Property(property="description", type="string", description="Description of the incident type"),
     *             @OA\Property(property="category_id", type="integer", description="Category ID for the incident type"),
     *             @OA\Property(property="priority", type="string", description="Priority of the incident type"),
     *             @OA\Property(property="sla_id", type="integer", description="SLA ID for the incident type")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Incident type created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Incident type created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the newly created incident type"),
     *                 @OA\Property(property="name", type="string", description="Name of the incident type"),
     *                 @OA\Property(property="description", type="string", description="Description of the incident type"),
     *                 @OA\Property(property="category_id", type="integer", description="Category ID"),
     *                 @OA\Property(property="priority", type="string", description="Priority level"),
     *                 @OA\Property(property="sla_id", type="integer", description="SLA ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating incident type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error creating Incident Type: SQL error")
     *         )
     *     )
     * )
     */
    public function store(IncidentTypeRequest $request)
    {
        $this->incidentTypeRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/incident-types/{id}",
     *     summary="Get a specific incident type",
     *     description="Retrieve the details of a specific incident type by its ID, including related category and SLA settings.",
     *     operationId="showIncidentType",
     *     tags={"IncidentTypes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the incident type to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Incident type retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="id", type="integer", description="ID of the incident type"),
     *                 @OA\Property(property="name", type="string", description="Name of the incident type"),
     *                 @OA\Property(property="description", type="string", description="Description of the incident type"),
     *                 @OA\Property(property="category_id", type="integer", description="Category ID associated with the incident type"),
     *                 @OA\Property(property="priority", type="string", description="Priority of the incident type"),
     *                 @OA\Property(property="sla_id", type="integer", description="SLA ID associated with the incident type"),
     *                     @OA\Property(property="department", type="object", 
     *                         @OA\Property(property="name", type="string", description="Department name")
     *                     )
     *                 ),
     *                 @OA\Property(property="sla_settings", type="object",
     *                     @OA\Property(property="name", type="string", description="SLA setting name")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Incident type not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Incident type not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error fetching incident type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error fetching Incident Type: SQL error")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
     return  $this->incidentTypeRepository->show($id);
    }


    /**
     * @OA\Put(
     *     path="/incident-types/{id}",
     *     summary="Update an existing incident type",
     *     description="Update the details of an existing incident type by its ID. The user must provide the fields to be updated, such as name, description, category, priority, and SLA.",
     *     operationId="updateIncidentType",
     *     tags={"IncidentTypes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the incident type to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data required to update the incident type",
     *         @OA\JsonContent(
     *             required={"name", "description", "category_id", "priority", "sla_id"},
     *             @OA\Property(property="name", type="string", description="Name of the incident type"),
     *             @OA\Property(property="description", type="string", description="Description of the incident type"),
     *             @OA\Property(property="category_id", type="integer", description="Category ID for the incident type"),
     *             @OA\Property(property="priority", type="string", description="Priority of the incident type"),
     *             @OA\Property(property="sla_id", type="integer", description="SLA ID for the incident type")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Incident type updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Incident type updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="ID of the updated incident type"),
     *                 @OA\Property(property="name", type="string", description="Name of the incident type"),
     *                 @OA\Property(property="description", type="string", description="Description of the incident type"),
     *                 @OA\Property(property="category_id", type="integer", description="Category ID"),
     *                 @OA\Property(property="priority", type="string", description="Priority level"),
     *                 @OA\Property(property="sla_id", type="integer", description="SLA ID")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Incident type not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Incident type not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating incident type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error updating Incident Type: SQL error")
     *         )
     *     )
     * )
     */

    public function update(IncidentTypeRequest $request, $id)
    {
       return $this->incidentTypeRepository->update($request,$id);
    }


    /**
 * @OA\Delete(
 *     path="/incident-types/{id}",
 *     summary="Delete a specific incident type",
 *     description="Delete an incident type by its ID. If the incident type is found, it will be deleted from the database.",
 *     operationId="destroyIncidentType",
 *     tags={"IncidentTypes"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID of the incident type to delete",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Incident type deleted successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Incident type deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Incident type not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Incident type not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Error deleting incident type",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Error deleting Incident Type: SQL error")
 *         )
 *     )
 * )
 */
    public function destroy($id)
    {
       return $this->incidentTypeRepository->delete($id);
    }
    
}
