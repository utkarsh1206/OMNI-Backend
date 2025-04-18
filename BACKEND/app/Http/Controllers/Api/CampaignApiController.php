<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignApiRequest;
use App\Http\Response\CustomApiResponse;
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
     *     summary="Fetch campaign list from remote Neox system",
     *     tags={"Campaign"},
     *     operationId="campaignListApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"domain_name"},
     *             @OA\Property(property="domain_name", type="string", example="clientdomain.com", description="Domain name to fetch configuration"),
     *             @OA\Property(property="user", type="string", example="admin", description="Neox system user"),
     *         )
     *     ),
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

            return sendHttpRequest($url, 'POST', $validatedData);
        } catch (Exception $e) {
            generateLogs('error', "Error in campaignListApi: ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
