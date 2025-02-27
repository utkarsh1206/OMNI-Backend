<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CronExpressionRepository;

class CronExpressionController extends Controller
{

    private $cronExpressionRepository;

    public function __construct(CronExpressionRepository $cronExpressionRepository)
    {
        $this->cronExpressionRepository = $cronExpressionRepository;
    }
    /**
     * @OA\Get(
     *     path="/api/cron-expressions",
     *     tags={"CronExpressions"},
     *     summary="List Cron Expressions",
     *     description="Retrieve a list of cron expressions with optional search and pagination.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for schedule type",
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
     *     @OA\Response(
     *         response=200,
     *         description="A list of cron expressions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="schedule_type", type="string"),
     *                     @OA\Property(property="schedule_expression", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request)
    {
          return $this->cronExpressionRepository->list($request);
    }
}
