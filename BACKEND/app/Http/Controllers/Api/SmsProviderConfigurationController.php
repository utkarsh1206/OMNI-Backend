<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsProviderConfigurationRequest;
use Illuminate\Http\Request;
use App\Repositories\SmsProviderConfigurationRepository;

class SmsProviderConfigurationController extends Controller
{
    private $smsProviderConfigurationRepository;

    public function __construct(SmsProviderConfigurationRepository $smsProviderConfigurationRepository)
    {
        $this->smsProviderConfigurationRepository = $smsProviderConfigurationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/sms-configurations",
     *     summary="List SMS Provider Configurations",
     *     description="Fetches a list of SMS provider configurations based on filters like provider name, method, enabled status, or search query.",
     *     operationId="listSmsConfigurations",
     *     tags={"SMS Configuration"},
     *     @OA\Parameter(
     *         name="provider_name",
     *         in="query",
     *         description="Filter by provider name",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="method",
     *         in="query",
     *         description="Filter by method",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Filter by enabled status",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter by provider name or config value",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Data fetch successful"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->smsProviderConfigurationRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/sms-configurations",
     *     summary="Create a new SMS provider and configurations",
     *     description="Creates a new SMS provider and its configurations based on the data provided in the request.",
     *     operationId="storeSmsProvider",
     *     tags={"SMS Provider"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data to create a new SMS provider and configurations",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"provider_name", "configurations"},
     *             @OA\Property(
     *                 property="provider_name",
     *                 type="string",
     *                 description="The name of the SMS provider",
     *                 example="Twilio"
     *             ),
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 description="Array of configurations for the SMS provider",
     *                 @OA\Items(
     *                     type="object",
     *                     additionalProperties={
     *                         "type": "string"
     *                     },
     *                     description="Key-value pairs for SMS provider configurations"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created SMS provider and configurations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider configuration added successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message details"
     *             )
     *         )
     *     )
     * )
     */
    public function store(SmsProviderConfigurationRequest $request)
    {
        return $this->smsProviderConfigurationRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sms-configurations/{providerId}",
     *     summary="Get SMS provider details by ID",
     *     description="Fetches the details of an SMS provider based on the provider ID.",
     *     operationId="showSmsProvider",
     *     tags={"SMS Provider"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         description="ID of the SMS provider",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched SMS provider details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Data fetched successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SMS provider not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message details"
     *             )
     *         )
     *     )
     * )
     */
    public function show($providerId)
    {
        return $this->smsProviderConfigurationRepository->show($providerId);
    }

    /**
     * @OA\Put(
     *     path="/api/sms-configurations/{providerId}",
     *     summary="Update SMS provider configurations",
     *     description="Updates the configurations of an SMS provider by its ID. The request body should contain the updated configurations.",
     *     operationId="updateSmsProviderConfigurations",
     *     tags={"SMS Provider"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         description="ID of the SMS provider to update configurations for",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body containing the configurations to update",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"configurations"},
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 description="Array of updated configurations for the SMS provider",
     *                 @OA\Items(
     *                     type="object",
     *                     additionalProperties={
     *                         "type": "string"
     *                     },
     *                     description="Key-value pairs for SMS provider configurations"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated SMS provider configurations",
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
     *                     @OA\Property(property="provider_id", type="integer"),
     *                     @OA\Property(property="config_key", type="string"),
     *                     @OA\Property(property="config_value", type="string"),
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider configurations updated successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SMS provider configurations not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider configurations not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Internal Server Error"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message details"
     *             )
     *         )
     *     )
     * )
     */
    public function update(SmsProviderConfigurationRequest $request, $providerId)
    {
        return $this->smsProviderConfigurationRepository->update($request, $providerId);
    }

    /**
     * @OA\Delete(
     *     path="/api/sms-configurations/{providerId}",
     *     summary="Delete SMS provider configurations",
     *     description="Deletes the configurations associated with the specified SMS provider by its ID.",
     *     operationId="deleteSmsProviderConfigurations",
     *     tags={"SMS Provider"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         description="ID of the SMS provider whose configurations are to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted SMS provider configurations",
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
     *                     @OA\Property(property="provider_id", type="integer"),
     *                     @OA\Property(property="config_key", type="string"),
     *                     @OA\Property(property="config_value", type="string")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider configurations deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SMS provider configurations not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="SMS provider not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Error message details"
     *             )
     *         )
     *     )
     * )
     */
    public function destroy($providerId)
    {
        return $this->smsProviderConfigurationRepository->delete($providerId);
    }
}
