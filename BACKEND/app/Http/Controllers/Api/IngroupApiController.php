<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\IngroupListRequest;
use Illuminate\Support\Facades\Validator;

class IngroupApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/ext-api/ingroup-list",
     *     summary="Get Ingroup List",
     *     description="Fetch the ingroup list from the external API. Domain name is required unless a JWT is provided.",
     *     operationId="ingroupListApi",
     *     tags={"Ingroup List"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"campaign_id, user, domain_name"},
     *                 @OA\Property(property="campaign_id", type="string", example="Incoming"),
     *                 @OA\Property(property="user", type="string", example="8002"),
     *                 @OA\Property(property="domain_name", type="string", example="Tenant4000"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Disposition list retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */

    public function ingroupListApi(Request $request)
    {
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

        // If there is an error in the config, return the error response
        if (isset($config['status']) && $config['status'] === 'error') {
            return response()->json($config);
        }

        $ip = $config['neox_local_ip'];
        $url = "https://{$ip}/api/admin/actions/v2/ingroup_list.php";

        // Get validation rules from FormRequest
        $rules = (new IngroupListRequest())->rules();

        // Validate request using extracted rules
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $validatedData = $validator->validated();
        return sendHttpRequest($url, 'POST', $validatedData);
    }
}
