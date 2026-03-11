<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordApiRequest;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;
use App\Repositories\RecordApiRepository;
use App\Services\ApiRequestService;
use Illuminate\Support\Facades\Validator;

class RecordApiController extends Controller
{
    private $apiRequestService;
    private $recordApiRepository;

    public function __construct(ApiRequestService $apiRequestService, RecordApiRepository $recordApiRepository)
    {
        $this->apiRequestService = $apiRequestService;
        $this->recordApiRepository = $recordApiRepository;
    }


    /**
     * @OA\Post(
     *     path="/api/ext-api/record-call",
     *     tags={"Call Recording"},
     *     description="Fetch call recording data from the external API.",
     *     operationId="callRecordingApi",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"action"},
     *                 @OA\Property(property="action", type="string", example="StartMonitor"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Call recording retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Validation failed or missing parameters."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized. Invalid or missing token."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Something went wrong while processing the request."
     *     )
     * )
     */

    public function callRecordingApi(Request $request)
    {
        // try {
        //     $config = getNeoxConfig();
        //     // Check configuration status and handle error centrally
        //     if (isset($config['status']) && $config['status'] === 'error') {
        //         return response()->json($config);
        //     }

        //     $ip = $config['neox_local_ip'];
        //     $neoxToken = $config['neox_token'];

        //     $url = "https://{$ip}/api/agent/actions/v2/agent_record.php";

        //     // Get validation rules from FormRequest
        //     $rules = (new RecordApiRequest())->rules();

        //     // Validate request using extracted rules
        //     $validator = Validator::make($request->all(), $rules);
        //     if ($validator->fails()) {
        //         return response()->json(['errors' => $validator->errors()]);
        //     }

        //     $validatedData = array_merge($validator->validated(), ['token' => $neoxToken]);
        //     return sendHttpRequest($url, 'POST', $validatedData);
        // } catch (Exception $e) {
        //     return $this->apiResponse->handleAndResponseException($e);
        // }

        $apiResult = $this->apiRequestService->handleApiRequest(
            $request,
            'agent_record',
            RecordApiRequest::class
        );

        if ($apiResult instanceof \Illuminate\Http\JsonResponse) {
            $apiResult = $apiResult->getData(true);
        }

        return $this->recordApiRepository->handleCallRecording($apiResult, $request);
    }
}
