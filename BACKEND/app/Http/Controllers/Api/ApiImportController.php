<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApiImportService;
use Illuminate\Http\Request;

class ApiImportController extends Controller
{
    protected ApiImportService $importService;

    public function __construct(ApiImportService $importService)
    {
        generateLogs('info', 'ApiImportController initialized');
        $this->importService = $importService;
    }

    /**
     * @OA\Post(
     *     path="/api/import",
     *     summary="Import data from an external API into a custom list table",
     *     tags={"API Import"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"list_id", "endpoint_id"},
     *             @OA\Property(property="list_id", type="integer", example=1, description="Target list ID"),
     *             @OA\Property(property="endpoint_id", type="integer", example=5, description="API endpoint ID to import from"),
     *             @OA\Property(
     *                 property="payload",
     *                 type="object",
     *                 description="Optional payload/query parameters to send to the API",
     *                 example={"user_id":123,"status":"active"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Import successful",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data inserted successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="failed_count", type="integer", example=2),
     *                 @OA\Property(property="inserted_count", type="integer", example=10),
     *                 @OA\Property(
     *                     property="imported_data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or import error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No mappings defined for this endpoint.")
     *         )
     *     )
     * )
     */
    public function import(Request $request)
    {
        generateLogs('info', "Import Function called");
        $validated = $request->validate([
            'list_id'     => 'required|integer|exists:ecdial_lists,list_id',
            'endpoint_id' => 'required|integer|exists:api_endpoints,id',
            'payload'     => 'nullable|array',
        ]);
        generateLogs('info', "Data Validated");
        $result = $this->importService->importData(
            $validated['list_id'],
            $validated['endpoint_id'],
            $validated['payload'] ?? []
        );
        generateLogs('info', "Import Function completed");
        return response()->json($result, $result['status'] === 'success' ? 200 : 400);
    }
}
