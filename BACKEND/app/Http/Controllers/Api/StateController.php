<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use App\Repositories\StateRepository;

class StateController extends Controller
{

    private $stateRepository;

    public function __construct(StateRepository $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }


     /**
     * @OA\Get(
     *     path="/api/states",
     *     summary="Retrieve a list of states",
     *     description="This API endpoint retrieves a list of states with optional search and pagination filters.",
     *     operationId="listStates",
     *     tags={"State"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter states (country_name, country_code, iso_code, status)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="country_id",
     *         in="query",
     *         description="Country ID to filter states by country",
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
     *         description="List of states",
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
     *                     @OA\Property(property="country_id", type="integer", example=1),
     *                     @OA\Property(property="country_name", type="string", example="Country Name"),
     *                     @OA\Property(property="country_code", type="string", example="CN"),
     *                     @OA\Property(property="iso_code", type="string", example="CN"),
     *                     @OA\Property(property="status", type="string", example="active"),
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
        return $this->stateRepository->list($request);
    }
}
