<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailLog;
use App\Http\Resources\EmailLogResource;
use Illuminate\Support\Facades\Redis;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
            // Get the search term from the request
            $searchTerm = $request->input('search', '');
            $query = EmailLog::with("user","email_template");
    
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
                $result = $query->paginate($perPage);
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
        $info =  EmailLog::find($id);
        return new EmailLogResource($info);
    }
}
