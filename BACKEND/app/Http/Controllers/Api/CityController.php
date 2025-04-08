<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CityRepository;

class CityController extends Controller
{
    private $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/cities",
     *     summary="Retrieve a list of cities",
     *     description="This API endpoint retrieves a list of cities with optional search and pagination filters based on state_id.",
     *     operationId="listCities",
     *     tags={"City"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter cities (country_name, country_code, iso_code, status).",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="state_id",
     *         in="query",
     *         description="State ID to filter cities by state.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Pagination page number. Pass 'all' to retrieve all records without pagination.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page. Defaults to 10.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of cities",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="city_name", type="string", example="City Name"),
     *                     @OA\Property(property="state_id", type="integer", example=10),
     *                     @OA\Property(property="country_name", type="string", example="Country Name"),
     *                     @OA\Property(property="country_code", type="string", example="US"),
     *                     @OA\Property(property="status", type="string", example="active")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Data fetched successfully"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     * )
     */

    public function index(Request $request)
    {
        return $this->cityRepository->list($request);
    }
}
