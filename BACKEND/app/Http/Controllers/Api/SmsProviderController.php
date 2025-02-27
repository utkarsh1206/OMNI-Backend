<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SmsProviderRepository;
use App\Http\Requests\UserSmsProviderRequest;

class SmsProviderController extends Controller
{

    private $smsProviderRepository;

    public function __construct(SmsProviderRepository $smsProviderRepository)
    {
        $this->smsProviderRepository = $smsProviderRepository;
    }


    
     /**
     * @OA\Get(
     *     path="api/sms-providers",
     *     summary="Get list of name prefixes",
     *     description="Fetches a list of name prefixes with optional search and pagination.",
     *     operationId="listSmsProviders",
     *     tags={"Sms Providers"},
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


    public function index()
    {
        return $this->smsProviderRepository->list();
    }


    /**
 * @OA\Post(
 *     path="/api/user-sms-provider",
 *     operationId="saveUserProvider",
 *     tags={"Sms Providers"},
 *     summary="Save or update a user's SMS provider configuration",
 *     description="Creates or updates the SMS provider configuration for the authenticated user.",
 *     @OA\RequestBody(
 *         required=true,
 *         description="Request body to save or update the user's SMS provider configuration",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="provider_id", type="integer", example=1, description="ID of the SMS provider")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successfully saved or updated the user SMS provider configuration",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success", description="Response status"),
 *             @OA\Property(property="message", type="string", example="Provider configurations created successfully", description="Response message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Internal Server Error"),
 *             @OA\Property(property="error", type="string", example="Error message from the exception")
 *         )
 *     )
 * )
 */

   
    public function saveUserProvider(UserSmsProviderRequest $request)
    {
        return $this->smsProviderRepository->saveUserProvider($request);
    }
}
