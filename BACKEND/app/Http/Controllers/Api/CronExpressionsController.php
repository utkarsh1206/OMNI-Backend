<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CronExpression;



class CronExpressionsController extends Controller
{
    public function index(Request $request)
    {
            // Get the search term from the request
            $searchTerm = $request->input('search', '');

            $query = CronExpression::select("id", "schedule_type", "schedule_expression");
    
            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('schedule_type', 'like', '%' . $searchTerm . '%');
                });
            }
    
            // Paginate the results
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            if ($page === 'all') {
                // If 'page' is 'all', retrieve all records without pagination
                $expressions = $query->get();
                return response()->json([
                    'data' => $expressions,
                ]);
            } else {
                // Paginate the results
                $expressions = $query->paginate($perPage);
                // Return the response with pagination meta
                return response()->json([
                    'data' => $expressions->items(),
                    'meta' => [
                        'current_page' => $expressions->currentPage(),
                        'last_page' => $expressions->lastPage(),
                        'per_page' => $expressions->perPage(),
                        'total' => $expressions->total(),
                    ],
                ]);
            }
    }
}
