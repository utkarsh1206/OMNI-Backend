<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\AccountRequest;
use App\Http\Controllers\Controller;
use App\Repositories\LicenseRepository;

class LicenseUpdateController extends Controller
{
    private $licenseRepository;

    public function __construct(LicenseRepository $licenseRepository)
    {
        $this->licenseRepository = $licenseRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/license_update",
     *     summary="Update User License Key",
     *     description="Update the license key for the authenticated user",
     *     operationId="updateLicense",
     *     tags={"License"},
     *     security={{"apiAuth": {}}},
     *     @OA\Parameter(
     *         name="license_key",
     *         in="query",
     *         description="The new license key",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="License key updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=0),
     *             @OA\Property(property="message", type="string", example="License updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Invalid data or missing parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Authentication failed or user not authorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request")
     *         )
     *     )
     * )
     */
    public function updateLicense(AccountRequest $request)
    {
        return $this->licenseRepository->updateLicense($request);
    }
}
