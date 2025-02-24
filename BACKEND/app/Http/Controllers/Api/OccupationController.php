<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\OccupationRepository;

/**
 * Class OccupationController
 *
 * @package App\Http\Controllers\Api
 */
class OccupationController extends Controller
{


    
    private $occupationRepository;

    public function __construct(OccupationRepository $occupationRepository)
    {
        $this->occupationRepository = $occupationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/occupations",
     *     summary="Retrieve a list of occupations",
     *     description="This API endpoint retrieves a list of occupations with optional search and pagination filters.",
     *     operationId="listOccupations",
     *     tags={"Occupation"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter occupations (country_name, country_code, iso_code, status).",
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
     *         description="List of occupations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="occupation_name", type="string", example="Software Developer"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                    
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
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
        return  $this->occupationRepository->list($request);
    }


}