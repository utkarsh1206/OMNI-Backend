<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ApiConfigurationTypeRepository;
use App\Http\Requests\UserSmsProviderRequest;

class ApiConfigurationTypeController extends Controller
{
    private $apiConfigurationTypeRepository;

    public function __construct(ApiConfigurationTypeRepository $ApiConfigurationTypeRepository)
    {
        $this->apiConfigurationTypeRepository = $ApiConfigurationTypeRepository;
    }

    /**
     * @OA\Get(
     *     path="api/api-configuration-types",
     *     summary="Get list of Api Configuration Types",
     *     description="Fetches a list of  with optional search and pagination.",
     *     operationId="listApiConfigurationTypes",
     *     tags={"Api Configuration Types"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter by country name, country code, ISO code, or status",
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
     *             example=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of Api Configuration Types successfully fetched",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Data fetched successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="country_name", type="string"),
     *                     @OA\Property(property="country_code", type="string"),
     *                     @OA\Property(property="iso_code", type="string"),
     *                     @OA\Property(property="status", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, possibly invalid query parameters",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function index()
    {
        return $this->apiConfigurationTypeRepository->list();
    }

  }
