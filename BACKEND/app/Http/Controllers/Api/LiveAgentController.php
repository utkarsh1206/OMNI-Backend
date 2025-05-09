<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\LiveAgentRepository;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="LiveAgents",
 *     description="API Endpoints for managing live agents"
 * )
 */
class LiveAgentController extends Controller
{
    protected $liveAgentRepo;

    public function __construct(LiveAgentRepository $liveAgentRepo)
    {
        $this->liveAgentRepo = $liveAgentRepo;
    }

    /**
     * @OA\Get(
     *     path="/api/live-agents",
     *     summary="Get all live agents",
     *     tags={"LiveAgents"},
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="campaign", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="ingroup", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of live agents")
     * )
     */
    public function index(Request $request)
    {
        return response()->json($this->liveAgentRepo->list($request));
    }

    /**
     * @OA\Post(
     *     path="/api/live-agents",
     *     summary="Create a new live agent",
     *     tags={"LiveAgents"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"user_id", "extension", "status", "campaign_id", "ingroup_id"},
     *         @OA\Property(property="user_id", type="string"),
     *         @OA\Property(property="extension", type="string"),
     *         @OA\Property(property="status", type="string"),
     *         @OA\Property(property="campaign_id", type="string"),
     *         @OA\Property(property="ingroup_id", type="string"),
     *         @OA\Property(property="status_last_updated_at", type="string", format="date"),
     *     )),
     *     @OA\Response(response=201, description="Live agent created")
     * )
     */
    public function store(Request $request)
    {
        $liveAgent = $this->liveAgentRepo->create($request);
        return response()->json($liveAgent, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/live-agents/{id}",
     *     summary="Get a specific live agent",
     *     tags={"LiveAgents"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Live agent data")
     * )
     */
    public function show($id)
    {
        return response()->json($this->liveAgentRepo->show($id));
    }

    /**
     * @OA\Put(
     *     path="/api/live-agents/{id}",
     *     summary="Update a live agent",
     *     tags={"LiveAgents"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="status", type="string"),
     *         @OA\Property(property="customer", type="string"),
     *     )),
     *     @OA\Response(response=200, description="Live agent updated")
     * )
     */
    public function update(Request $request, $id)
    {
        return response()->json($this->liveAgentRepo->update($request, $id));
    }

    /**
     * @OA\Delete(
     *     path="/api/live-agents/{id}",
     *     summary="Delete a live agent",
     *     tags={"LiveAgents"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Live agent deleted")
     * )
     */
    public function destroy($id)
    {
        return response()->json($this->liveAgentRepo->delete($id));
    }
}
