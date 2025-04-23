<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialLists;
use Exception;

class CampaignApiController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/camp-list",
     *     summary="Fetch and filter campaign list from remote Neox system",
     *     tags={"Campaign"},
     *     operationId="filteredCampaignListApi",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"domain_name"},
     *             @OA\Property(
     *                 property="domain_name",
     *                 type="string",
     *                 example="TestTenant13.com",
     *                 description="Domain name to fetch configuration"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="string",
     *                 example="8002",
     *                 description="Neox system user"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered campaigns from Neox system",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Campaign list retrieved successfully",
     *                 "data": {
     *                     {
     *                         "campaign_id": "Incoming",
     *                         "campaign_name": "Incoming",
     *                         "dial_method": "INBOUND_MAN"
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No valid campaigns found or bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"message": "No valid campaigns found"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Unexpected error occurred"}
     *         )
     *     )
     * )
     */

    public function campaignListApi(CampaignApiRequest $request)
    {
        try {
            $domainName = $request->domain_name;
            if (!$domainName) {
                return response()->json(['error' => 'Domain name is required']);
            }

            $config = getNeoxConfig($domainName);
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];
            $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";
            $validatedData = $request->validated();

            // Send request to external API
            $apiResponse = sendHttpRequest($url, 'POST', $validatedData);
            $apiData = json_decode($apiResponse->getContent(), true);

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return response()->json($apiData); // Early exit if format is unexpected
            }

            // Get DB connection
            $connection = getDbConnection($domainName);

            // Retrieve allowed campaign_ids from EcdialLists table
            $campaignIds = $connection
                ? $connection->table('ecdial_lists')->pluck('campaign_id')->toArray()
                : EcdialLists::pluck('campaign_id')->toArray();

            // Filter the API response data
            $filteredCampaigns = array_filter($apiData['data'], function ($campaign) use ($campaignIds) {
                return in_array($campaign['campaign_id'], $campaignIds);
            });

            // Replace original data with filtered campaigns
            // $apiData['data'] = array_values($filteredCampaigns);
            if (empty($filteredCampaigns)) {
                return response()->json([
                    'message' => 'No valid campaigns found',
                ], 400);
            }

            $apiData['data'] = array_values($filteredCampaigns);

            return response()->json($apiData);
        } catch (Exception $e) {
            generateLogs('error', "Error in campaignListApi: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/camp-list-all",
     *     summary="Fetch campaign list from remote Neox system",
     *     tags={"Campaign"},
     *     operationId="campaignListAllApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "status": "success",
     *                 "data": {
     *                     {
     *                         "campaign_id": "CAMP123",
     *                         "campaign_name": "Sales Campaign",
     *                         "dial_method": "INBOUND_MAN"
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Domain name is required"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Unexpected error occurred"}
     *         )
     *     )
     * )
     */

    public function campaignListAllApi(CampaignApiRequest $request)
    {
        try {
            // $domainName = $request->domain_name;
            // if (!$domainName) {
            //     return response()->json(['error' => 'Domain name is required']);
            // }

            // $config = getNeoxConfig($domainName);

            // Check if JWT is not provided
            if ($request->isJWT == 'No') {
                $domainName = $request->domain_name;
                if (!$domainName) {
                    return response()->json(['error' => 'Domain name is required']);
                }
            } else {
                // If JWT is present, domain name is not required
                $domainName = null;
            }

            // Retrieve configuration
            $config = getNeoxConfig($domainName);

            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];

            $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";
            $validatedData = $request->validated();

            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            generateLogs('error', "Error in campaignListApi: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/camp-list-filtered",
     *     summary="Fetch campaigns from Neox and exclude already existing campaigns",
     *     tags={"Campaign"},
     *     operationId="campaignListFilteredApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Filtered campaign list retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "code": 0,
     *                 "message": "Filtered campaign list retrieved successfully",
     *                 "data": {
     *                     {
     *                         "campaign_id": "CAMP999",
     *                         "campaign_name": "New Campaign",
     *                         "dial_method": "OUTBOUND_MAN"
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Domain name is required"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"error": "Unexpected error occurred"}
     *         )
     *     )
     * )
     */

    public function campaignListFilteredApi(CampaignApiRequest $request)
    {
        try {
            // Check if JWT is not provided
            if ($request->isJWT == 'No') {
                $domainName = $request->domain_name;
                if (!$domainName) {
                    return response()->json(['error' => 'Domain name is required']);
                }
            } else {
                // If JWT is present, domain name is not required
                $domainName = null;
            }

            // Retrieve configuration
            $config = getNeoxConfig($domainName);
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];
            $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";
            $validatedData = $request->validated();

            // Send HTTP request
            $response = sendHttpRequest($url, 'POST', $validatedData);

            // Convert JsonResponse to array
            $responseData = $response->getData(true);

            if (isset($responseData['code']) && $responseData['code'] === 0 && isset($responseData['data'])) {
                $campaigns = $responseData['data'];

                // Get existing campaign IDs from ecdial_lists table
                $existingCampaignIds = EcdialLists::pluck('campaign_id')->toArray();

                // Filter out campaigns already present
                $filteredCampaigns = array_filter($campaigns, function ($campaign) use ($existingCampaignIds) {
                    return !in_array($campaign['campaign_id'], $existingCampaignIds);
                });

                return response()->json([
                    'code' => 0,
                    'message' => 'Filtered campaign list retrieved successfully',
                    'data' => array_values($filteredCampaigns)
                ]);
            }

            return $response;
        } catch (Exception $e) {
            generateLogs('error', "Error in campaignListFilteredApi: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
