<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessType;

class BusinessTypeController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = BusinessType::query();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $business_type = $query->get();
            return response()->json([
                'data' => $business_type,
            ]);
        } else {
            // Paginate the results
            $business_type = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $business_type->items(),
                'meta' => [
                    'current_page' => $business_type->currentPage(),
                    'last_page' => $business_type->lastPage(),
                    'per_page' => $business_type->perPage(),
                    'total' => $business_type->total(),
                ],
            ]);
        }
    }
}
