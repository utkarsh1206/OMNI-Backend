<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessType;
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
     * @SWG\Get(
     *     path="/api/business-types",
     *     summary="Get a list of business types",
     *     description="Retrieve a list of business types with optional search and pagination.",
     *     produces={"application/json"},
     *     tags={"BusinessType"},
     *     @SWG\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter business types by title or status",
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
     *         description="A list of business types",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="data",
     *                 type="array",
     *                 @SWG\Items(ref="#/definitions/BusinessType")
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
        return $this->businessTypeRepository->list($request);
    }
}
