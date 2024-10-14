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
            $countries = $query->paginate($perPage);
    
            // Return the response
            return response()->json([
                'data' => $countries->items(),
                'meta' => [
                    'current_page' => $countries->currentPage(),
                    'last_page' => $countries->lastPage(),
                    'per_page' => $countries->perPage(),
                    'total' => $countries->total(),
                ],
            ]);
    }
}
