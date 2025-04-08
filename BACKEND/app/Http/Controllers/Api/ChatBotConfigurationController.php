<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsProviderConfigurationRequest;
use Illuminate\Http\Request;
use App\Repositories\ChatBotConfigurationRepository;

class ChatBotConfigurationController extends Controller
{
    private $chatBotConfigurationRepository;

    public function __construct(ChatBotConfigurationRepository $chatBotConfigurationRepository)
    {
        $this->chatBotConfigurationRepository = $chatBotConfigurationRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/sms-provider-configurations",
     *     tags={"Sms Provider Configurations"},
     *     summary="List SMS Provider Configurations",
     *     description="Retrieve a list of SMS provider configurations with optional search filters.",
     *     @OA\Parameter(
     *         name="provider_name",
     *         in="query",
     *         description="Filter by provider name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="method",
     *         in="query",
     *         description="Filter by method",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Filter by enabled status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of SMS Provider Configurations",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="provider_id", type="integer"),
     *                     @OA\Property(property="provider_name", type="string"),
     *                     @OA\Property(
     *                         property="configurations",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="config_key", type="string"),
     *                             @OA\Property(property="config_value", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->chatBotConfigurationRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/sms-provider-configurations",
     *     tags={"Sms Provider Configurations"},
     *     summary="Create SMS Provider Configuration",
     *     description="Create a new SMS provider and its configurations.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"provider_name", "configurations"},
     *             @OA\Property(property="provider_name", type="string", example="Twilio"),
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="key", type="string"),
     *                     @OA\Property(property="value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS Provider Configuration created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sms Provider Configuration created successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function store(SmsProviderConfigurationRequest $request)
    {
        return $this->chatBotConfigurationRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sms-provider-configurations/{providerId}",
     *     tags={"Sms Provider Configurations"},
     *     summary="Show SMS Provider Configuration",
     *     description="Retrieve the details of a specific SMS provider configuration.",
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         description="The ID of the SMS provider configuration.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the SMS provider configuration",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function show($providerId)
    {
        return $this->chatBotConfigurationRepository->show($providerId);
    }


    /**
     * @OA\Put(
     *     path="/api/sms-provider-configurations/{providerId}",
     *     tags={"Sms Provider Configurations"},
     *     summary="Update SMS Provider Configuration",
     *     description="Update the configurations of a specific SMS provider.",
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         description="The ID of the SMS provider configuration to update.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"configurations"},
     *             @OA\Property(
     *                 property="configurations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="key", type="string"),
     *                     @OA\Property(property="value", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS Provider Configuration updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sms Provider Configuration updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function update(SmsProviderConfigurationRequest $request, $providerId)
    {
        return $this->chatBotConfigurationRepository->update($request, $providerId);
    }

    /**
     * @OA\Delete(
     *     path="/api/sms-provider-configurations/{providerId}",
     *     tags={"Sms Provider Configurations"},
     *     summary="Delete SMS Provider Configuration",
     *     description="Delete a specific SMS provider configuration.",
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         description="The ID of the SMS provider configuration to delete.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS Provider Configuration deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Sms Provider Configuration deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function destroy($providerId)
    {
        return $this->chatBotConfigurationRepository->delete($providerId);
    }
}
