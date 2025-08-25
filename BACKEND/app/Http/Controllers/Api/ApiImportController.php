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
