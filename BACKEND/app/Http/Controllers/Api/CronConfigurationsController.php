<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CronsResource;
use App\Models\CronConfiguration;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CronConfigurationsController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Filter Conditions
        $commandSignature = $request->input('command_signature');
        $scheduleType = $request->input('schedule_type');
        $status = $request->input('status');

        // Start the query for tenants
        $query = CronConfiguration::select("id", "command_signature", "schedule_type", "schedule_expression");

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('command_signature', 'like', '%' . $searchTerm . '%')
                    ->orwhere('schedule_type', 'like', '%' . $searchTerm . '%')
                    ->orwhere('schedule_expression', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($commandSignature) {
            $query->where('command_signature', $commandSignature);
        }

        if ($scheduleType) {
            $query->where('schedule_type', $scheduleType);
        }

        if ($status) {
            $query->where('status', $status);
        }
        
        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $cronConfiguration = $query->get();
            return response()->json([
                'data' => $cronConfiguration,
            ]);
        } else {
            // Paginate the results
            $cronConfiguration = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $cronConfiguration->items(),
                'meta' => [
                    'current_page' => $cronConfiguration->currentPage(),
                    'last_page' => $cronConfiguration->lastPage(),
                    'per_page' => $cronConfiguration->perPage(),
                    'total' => $cronConfiguration->total(),
                ],
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'command_signature' => 'required',
                'schedule_type' => 'required',
                'schedule_expression' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
        
            $cronConfiguration = CronConfiguration::create([
                'command_signature' => $request->command_signature,
                'schedule_type' => $request->schedule_type,
                'schedule_expression' => $request->schedule_expression,
                'status' => isset($request->status) ? $request->status : "active"
            ]);

            return response()->json([
                'message' => ' Cron Configuration created successfully!',
                'data' => new CronsResource($cronConfiguration)
            ], 200);
        
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(CronConfiguration $cronConfiguration)
    {
        return new CronsResource($cronConfiguration);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'command_signature' => 'required',
                'schedule_type' => 'required',
                'schedule_expression' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $cronConfiguration = CronConfiguration::find($id);

            if (!$cronConfiguration) {
                return response()->json([
                    'message' => 'Cron Configuration not found.',
                ], 404);
            }
            
            $cronConfiguration->update($request->all());

            return response()->json([
                'message' => 'Cron Configuration updated successfully!',
                'data' => new CronsResource($cronConfiguration)
            ], 200);
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cronConfiguration = CronConfiguration::find($id);

            if (!$cronConfiguration) {
                return response()->json([
                    'message' => 'Cron Configuration not found.',
                ], 404);
            }
            
            $cronConfiguration->delete();

            return response()->json([
                'message' => ' Cron Configuration deleted successfully!',
                'data' => new CronsResource($cronConfiguration)
            ], 200);
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);
            
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }
}
