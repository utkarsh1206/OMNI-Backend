<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\LiveCallDashboardRepository;

class LiveCallDashboardController extends Controller
{
    protected $liveCallRepository;

    // Inject the repository into the controller
    public function __construct(LiveCallDashboardRepository $liveCallRepository)
    {
        $this->liveCallRepository = $liveCallRepository;
    }


    /**
     * @OA\Get(
     *     path="/api/live-calls",
     *     summary="Get Live Call Logs",
     *     description="Retrieve all current live calls with status (`ringing` or `incall`) and duration since `call_initiated`.",
     *     operationId="getLiveCalls",
     *     tags={"Live Calls"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Start date of the time range (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="End date of the time range (format: YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Live calls fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="No live calls found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Record not found")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */

    public function index()
    {
        return $this->liveCallRepository->getLiveCalls();
    }
}
