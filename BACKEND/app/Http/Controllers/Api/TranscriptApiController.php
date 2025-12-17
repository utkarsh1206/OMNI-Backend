<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranscriptApiRequest;
use App\Http\Response\CustomApiResponse;
use App\Services\ApiRequestService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptApiController extends Controller
{
    private $apiRequestService;
    private $apiResponse;

    public function __construct(ApiRequestService $apiRequestService, CustomApiResponse $customApiResponse)
    {
        $this->apiRequestService = $apiRequestService;
        $this->apiResponse = $customApiResponse;
    }


    /**
     * @OA\Post(
     *     path="/api/transcript",
     *     summary="Generate or retrieve call transcript PDF",
     *     tags={"Transcript"},
     *     security={{"bearerAuth": {}}},
     *     operationId="transcriptApi",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file_path"},
     *             @OA\Property(
     *                 property="file_path",
     *                 type="string",
     *                 example="indeng.wav",
     *                 description="Full file path or just the file name (default path will be added if only filename is provided)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="generated_pdf_url", type="string", example="/storage/transcripts/response_indeng.pdf"),
     *             @OA\Property(property="details",type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Transcript generation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Failed to generate transcript.")
     *         )
     *     )
     * )
     */
    public function transcriptApi(Request $request)
    {
        try {
            // Retrieve configuration
            $config = getNeoxConfig();

            // If there is an error in the config, return the error response
            if (isset($config['status']) && $config['status'] === 'error') {
                return response()->json($config);
            }

            $ip = $config['neox_local_ip'];
            $url = "https://{$ip}/admin/file_browse/call_transcript_omni.php";

            // Get validation rules from FormRequest
            $rules = (new TranscriptApiRequest())->rules();

            // Validate request using extracted rules
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()]);
            }

            $validatedData = $validator->validated();

            // Default directory if only filename is provided
            $defaultDirectory = '/opt/CLRNdialcenter/bin/php/admin/file_browse/recordings/';
            $filePath = $validatedData['file_path'];

            // If no directory in file_path, prepend default directory
            if (!str_contains($filePath, '/')) {
                $filePath = rtrim($defaultDirectory, '/') . '/' . ltrim($filePath, '/');
                $validatedData['file_path'] = $filePath;
            }

            // Extract base name from file_path (without extension)
            $fileBaseName = pathinfo($filePath, PATHINFO_FILENAME);
            $pdfFileName = 'response_' . $fileBaseName . '.pdf';
            $pdfFilePath = 'transcripts/' . $pdfFileName;

            // Check if the file already exists
            if (Storage::disk('public')->exists($pdfFilePath)) {
                $publicUrl = Storage::url($pdfFilePath);
                return response()->json([
                    'message' => 'PDF already exists.',
                    'generated_pdf_url' => $publicUrl,
                ]);
            }

            // Call the external API
            $response = Http::timeout(0)->post($url, $validatedData);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to generate transcript.'], 500);
            }

            $responseData = $response->json();
            
            if (!empty($responseData['details'])) {
                $html = '';

                foreach ($responseData['details'] as $detail) {
                    if (!empty($detail['speaker']))
                    {
                        $speaker = ($detail['speaker'] === 'Speaker 1') ? 'AGENT' : 'CUSTOMER';
                        $html .= '<p><strong>' . $speaker . ':</strong> ' . $detail['text'] . '</p>';
                    }
                    else
                    {
                        $html .= "<p>" . e($detail) . "</p>";
                    }

                }

                // Generate the PDF
                $pdf = Pdf::loadHTML($html);

                // Save the PDF
                Storage::disk('public')->put($pdfFilePath, $pdf->output());

                // Get public URL
                $publicUrl = Storage::url($pdfFilePath);

                // Add the public URL to the response
                $responseData['generated_pdf_url'] = $publicUrl;
            }

            return response()->json($responseData);
        } catch (Exception $e) {
            generateLogs("error", "An unexpected error occurred TranscriptApiController@transcriptApi ", $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
