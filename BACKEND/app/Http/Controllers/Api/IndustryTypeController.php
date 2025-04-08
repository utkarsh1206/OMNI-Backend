<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\IndustryTypeRepository;

class IndustryTypeController extends Controller
{
    private $industryTypeRepository;

    public function __construct(IndustryTypeRepository $industryTypeRepository)
    {
        $this->industryTypeRepository = $industryTypeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/industry_type",
     *     summary="Get a list of industry types",
     *     description="Fetches a list of industry types with optional search and pagination.",
     *     operationId="getIndustryTypes",
     *     tags={"Industry Types"},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering industry types by title or status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of records per page (default is 10)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination or 'all' to get all records",
     *         required=false,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return  $this->industryTypeRepository->list($request);
    }
}
