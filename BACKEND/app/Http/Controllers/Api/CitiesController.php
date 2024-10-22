<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cities;

class CitiesController extends Controller
{
    public function index(Request $request)
    {
            // Get the search term from the request
            $searchTerm = $request->input('search', '');
            $state_id = $request->input('state_id', '');

            $query = Cities::query()->where('is_delete', 'No')->where('state_id',$state_id);
    
            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('country_name', 'like', '%' . $searchTerm . '%')
                        ->orwhere('country_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('iso_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('status', 'like', '%' . $searchTerm . '%');
                });
            }
    
            // Paginate the results
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            if ($page === 'all') {
                // If 'page' is 'all', retrieve all records without pagination
                $cities = $query->get();
                return response()->json([
                    'data' => $cities,
                ]);
            } else {
                // Paginate the results
                $cities = $query->paginate($perPage);
                // Return the response with pagination meta
                return response()->json([
                    'data' => $cities->items(),
                    'meta' => [
                        'current_page' => $cities->currentPage(),
                        'last_page' => $cities->lastPage(),
                        'per_page' => $cities->perPage(),
                        'total' => $cities->total(),
                    ],
                ]);
            }
    }
}
