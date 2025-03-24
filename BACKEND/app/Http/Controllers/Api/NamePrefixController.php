<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nameprefix;
use App\Repositories\NamePrefixRepository;

class NamePrefixController extends Controller
{

    private $nameSpacePrefix;

    public function __construct(NamePrefixRepository $nameSpacePrefix)
    {
        $this->nameSpacePrefix = $nameSpacePrefix;
    }

      /**
     * @OA\Get(
     *     path="api/nameprefixes",
     *     summary="Get list of name prefixes",
     *     description="Fetches a list of name prefixes with optional search and pagination.",
     *     operationId="listNamePrefixes",
     *     tags={"Name Prefixes"},
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
     *         description="List of name prefixes successfully fetched",
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

    public function index(Request $request)
    {
        return $this->nameSpacePrefix->list($request);
    }
}
