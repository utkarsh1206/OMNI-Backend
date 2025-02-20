<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndustryType;

class IndustryTypeController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = IndustryType::query();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%');
            });
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            $industry_type = $query->get();
            return response()->json([
                'data' => $industry_type,
            ]);
        } else {
            // Paginate the results
            $industry_type = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $industry_type->items(),
                'meta' => [
                    'current_page' => $industry_type->currentPage(),
                    'last_page' => $industry_type->lastPage(),
                    'per_page' => $industry_type->perPage(),
                    'total' => $industry_type->total(),
                ],
            ]);
        }
    }
}
