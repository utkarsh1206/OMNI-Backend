<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\States;


class StatesController extends Controller
{
    public function index(Request $request)
    {

            // Get the search term from the request
            $searchTerm = $request->input('search', '');
            $country_id = $request->input('country_id', '');

            $query = States::query()->where('is_delete', 'No')->where('country_id',$country_id);
    
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
            $states = $query->paginate($perPage);
    
            // Return the response
            return response()->json([
                'data' => $states->items(),
                'meta' => [
                    'current_page' => $states->currentPage(),
                    'last_page' => $states->lastPage(),
                    'per_page' => $states->perPage(),
                    'total' => $states->total(),
                ],
            ]);
    }
}
