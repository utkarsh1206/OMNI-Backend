<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CronCommandRepository;
use Swagger\Annotations as SWG;

class CronCommandController extends Controller
{

    private $cronCommandRepository;
    public function __construct(CronCommandRepository $cronCommandRepository)
    {
        $this->cronCommandRepository = $cronCommandRepository;
    }

     /**
     * @SWG\Get(
     *     path="/api/cron-commands",
     *     summary="Get a list of cron commands",
     *     description="Retrieve a list of cron commands with optional search and pagination.",
     *     produces={"application/json"},
     *     tags={"CronCommand"},
     *     @SWG\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter cron commands by command_signature",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page for pagination",
     *         required=false,
     *         type="integer",
     *         default=10
     *     ),
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination, or 'all' for all records without pagination",
     *         required=false,
     *         type="string",
     *         default="1"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="A list of cron commands",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="data",
     *                 type="array",
     *                 @SWG\Items(ref="#/definitions/CronCommand")
     *             ),
     *             @SWG\Property(
     *                 property="meta",
     *                 type="object",
     *                 @SWG\Property(property="current_page", type="integer"),
     *                 @SWG\Property(property="last_page", type="integer"),
     *                 @SWG\Property(property="per_page", type="integer"),
     *                 @SWG\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid pagination parameters"
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

    public function index(Request $request)
    {
        return $this->cronCommandRepository->list($request);
    }
}
