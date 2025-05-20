<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\AuthTokenRepository;
use App\Models\Detail;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    private $authTokenRepository;

    public function __construct(AuthTokenRepository $authTokenRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/generate-auth-token",
     *     tags={"External Use"},
     *     summary="Retrieve a valid auth token",
     *     description="This endpoint returns an existing valid auth token if available, or generates a new one. Requires a valid UUID that exists in the `details` table.",
     *     operationId="generateAuthToken",
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         description="UUID",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid",
     *             example="8f45c618-75fb-4471-ba61-c539efd69dd0"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token generated or retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="your_generated_token_here"),
     *                 @OA\Property(property="ipaddress", type="string", example="127.0.0.1"),
     *                 @OA\Property(property="expire_at", type="string", format="date-time", example="2025-04-24T17:30:00Z"),
     *                 @OA\Property(property="status", type="string", example="New")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The id is missing.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function index(Request $request, Detail $detail)
    {
        return $this->authTokenRepository->list($request, $detail);
    }
}
