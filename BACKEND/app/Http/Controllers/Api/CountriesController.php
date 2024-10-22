<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Countries;



class CountriesController extends Controller
{
    public function index(Request $request)
    {
            // Get the search term from the request
            $searchTerm = $request->input('search', '');

            $query = Countries::query()->where('is_delete', 'No');
    
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
                $countires = $query->get();
                return response()->json([
                    'data' => $countires,
                ]);
            } else {
                // Paginate the results
                $countires = $query->paginate($perPage);
                // Return the response with pagination meta
                return response()->json([
                    'data' => $countires->items(),
                    'meta' => [
                        'current_page' => $countires->currentPage(),
                        'last_page' => $countires->lastPage(),
                        'per_page' => $countires->perPage(),
                        'total' => $countires->total(),
                    ],
                ]);
            }
    }
}
