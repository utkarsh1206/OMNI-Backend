<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgentActivityRequest;
use App\Repositories\AgentActivityRepository;

class AgentActivityController extends Controller
{
  
  private $agentActivityRepository;
  public function __construct(AgentActivityRepository $agentActivityRepository)
  {
    $this->agentActivityRepository = $agentActivityRepository;
  }

    /**
 * @OA\Get(
 *     path="/api/agent-activity",
 *     summary="Get Agent Activity",
 *     description="Fetches the agent activity based on specified filters and returns the results in a specific format (JSON, CSV, PDF, or Graph).",
 *     operationId="getAgentActivity",
 *     tags={"Agent Activity"},
 *     @OA\Parameter(
 *         name="users",
 *         in="query",
 *         required=true,
 *         description="List of user IDs to filter by",
 *         @OA\Schema(
 *             type="array",
 *             @OA\Items(type="integer")
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="campaign",
 *         in="query",
 *         required=false,
 *         description="Campaign ID to filter by",
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         required=true,
 *         description="Start date for filtering the activities (format: Y-m-d)",
 *         @OA\Schema(
 *             type="string",
 *             format="date"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         required=true,
 *         description="End date for filtering the activities (format: Y-m-d)",
 *         @OA\Schema(
 *             type="string",
 *             format="date"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Number of items to display per page (defaults to 15)",
 *         @OA\Schema(
 *             type="integer",
 *             default=15
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="format",
 *         in="query",
 *         required=false,
 *         description="The format of the response (json, csv, pdf, graph). Default is json.",
 *         @OA\Schema(
 *             type="string",
 *             enum={"json", "csv", "pdf", "graph"},
 *             default="json"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully retrieved agent activity data.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 description="List of agent activities"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation or Processing failed.",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="error",
 *                 type="string",
 *                 example="Validation or Processing failed"
 *             ),
 *             @OA\Property(
 *                 property="messages",
 *                 type="string",
 *                 example="Some validation error message."
 *             )
 *         )
 *     ),
 * )
 */
    // Get agent activity listing
    public function getAgentActivity(AgentActivityRequest $request)
    {
        return $this->agentActivityRepository->index($request);
    }

  }
