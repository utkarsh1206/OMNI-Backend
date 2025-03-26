<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\BusinessTypeRepository;
use Swagger\Annotations as SWG;

class BusinessTypeController extends Controller
{
    private $businessTypeRepository;

    public function __construct(BusinessTypeRepository $businessTypeRepository)
    {
        $this->businessTypeRepository = $businessTypeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/business-types",
     *     summary="Get a list of business types",
     *     description="Retrieve a list of business types with optional search and pagination.",
     *     tags={"BusinessType"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter business types by title or status",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination, or 'all' for all records without pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             default="1"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of business types",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Retail"),
     *                     @OA\Property(property="status", type="string", example="Active")
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
     *         response=400,
     *         description="Invalid pagination parameters"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->businessTypeRepository->list($request);
    }
}
