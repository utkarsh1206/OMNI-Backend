<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Http\Resources\AuditLogResource;
use Illuminate\Support\Facades\Redis;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        // $query = ActivityLog::where('user_id', auth()->user()->id);

        // Check if the authenticated user's user_id is 0 or not
        if (auth()->user()->id == 1) {
            // If user_id is 0, fetch all records
            $query = ActivityLog::query();
        } else {
            // Otherwise, fetch only the authenticated user's data
            $query = ActivityLog::where('user_id', auth()->user()->id);
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $result = $query->get();
            return response()->json([
                'data' => $result,
            ]);
        } else {
            // Paginate the results
            $result = $query->orderBy('created_at','desc')->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $result->items(),
                'meta' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ],
            ]);
        }
    }

    public function show($id)
    {
        $info =  ActivityLog::find($id);
        return new AuditLogResource($info);
    }
}
