<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CronConfiguration;
use App\Repositories\CronConfigurationRepository;
use App\Http\Requests\CronConfigurationRequest;

class CronConfigurationController extends Controller
{
    private $cronConfigurationRepository;

    public function __construct(CronConfigurationRepository $cronConfigurationRepository)
    {
        $this->cronConfigurationRepository = $cronConfigurationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/cron-configurations",
     *     tags={"Cron Configurations"},
     *     summary="List Cron Configurations",
     *     description="Retrieve a list of cron configurations with optional search and pagination.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for command signature, schedule type, or schedule expression",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination. Pass 'all' to fetch all records",
     *         required=false,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Parameter(
     *         name="command_signature",
     *         in="query",
     *         description="Filter by command signature",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="schedule_type",
     *         in="query",
     *         description="Filter by schedule type",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (active/inactive)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of cron configurations",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->cronConfigurationRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/cron-configurations",
     *     tags={"Cron Configurations"},
     *     summary="Create a new Cron Configuration",
     *     description="Create a new cron configuration.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"command_signature", "schedule_type", "schedule_expression"},
     *             @OA\Property(property="command_signature", type="string"),
     *             @OA\Property(property="schedule_type", type="string"),
     *             @OA\Property(property="schedule_expression", type="string"),
     *             @OA\Property(property="status", type="string", default="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cron configuration created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function store(CronConfigurationRequest $request)
    {
        return $this->cronConfigurationRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/cron-configurations/{id}",
     *     tags={"Cron Configurations"},
     *     summary="Get a single Cron Configuration",
     *     description="Retrieve a single cron configuration by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A single cron configuration"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cron configuration not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show(CronConfiguration $cronConfiguration)
    {
        return $this->cronConfigurationRepository->show($cronConfiguration);
    }

    /**
     * @OA\Put(
     *     path="/api/cron-configurations/{id}",
     *     tags={"Cron Configurations"},
     *     summary="Update a Cron Configuration",
     *     description="Update an existing cron configuration.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"command_signature", "schedule_type", "schedule_expression"},
     *             @OA\Property(property="command_signature", type="string"),
     *             @OA\Property(property="schedule_type", type="string"),
     *             @OA\Property(property="schedule_expression", type="string"),
     *             @OA\Property(property="status", type="string", default="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cron configuration updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cron configuration not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function update(CronConfigurationRequest $request, $id)
    {
        return $this->cronConfigurationRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/cron-configurations/{id}",
     *     tags={"Cron Configurations"},
     *     summary="Delete a Cron Configuration",
     *     description="Delete a cron configuration by ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cron configuration deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Cron configuration not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->cronConfigurationRepository->delete($id);
    }
}
