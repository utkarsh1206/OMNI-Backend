<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DispositionListRequest;
use App\Http\Response\CustomApiResponse;
use App\Services\ApiRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispositionApiController extends Controller
{
    private $apiResponse;
    private $apiRequestService;

    public function __construct(CustomApiResponse $customApiResponse, ApiRequestService $apiRequestService)
    {
        $this->apiResponse = $customApiResponse;
        $this->apiRequestService = $apiRequestService;
    }

    /**
     * @OA\Post(
     *     path="/api/ext-api/dispo-list",
     *     tags={"Disposition List"},
     *     description="Fetch the disposition list from the external API.",
     *     operationId="dispositionListApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"campaign_id"},
     *                 @OA\Property(property="campaign_id", type="string", example="Incoming"),
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function dispositionListApi(Request $request)
    {
        $config = getNeoxConfig();
        // Check configuration status and handle error centrally
        if (isset($config['status']) && $config['status'] === 'error') {
            return response()->json($config);
        }

        $ip = $config['neox_local_ip'];
        if (!$ip) {
            return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.local_ip_not_found'));
        }

        // NOC-996, TODO DC-Hosted integration, Condition based so omni run with multi tenant and without multitenant.
        if (config('omni.IS_DC_MULTITENANT') == 1) {
            if ($request->has('user')) {
                $request->merge([
                    'user_id' => $request->input('user')
                ]);

                $request->offsetUnset('user');
            }

            return $this->apiRequestService->handleApiRequest(
                $request,
                'disposition_list',
                DispositionListRequest::class,
            );
        } else {
            $url = "https://{$ip}/api/admin/actions/v2/disposition_list.php";


            // Get validation rules from FormRequest
            $rules = (new DispositionListRequest())->rules();

            // Validate request using extracted rules
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()]);
            }

            $validatedData = $validator->validated();

            return sendHttpRequest($url, 'POST', $validatedData);
        }
        // NOC-996, END
    }
}
