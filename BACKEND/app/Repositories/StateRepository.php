<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\State;
use App\Http\Response\CustomApiResponse;

class StateRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        $country_id = $request->input('country_id', '');

        $query = State::query()->where('country_id', $country_id);

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
            $states = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $states, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $states = $query->paginate($perPage);
            // Return the response with pagination meta
            return $this->apiResponse->getResponseStructure(trans('message.success'), $states, trans('message.data_fetch'));
        }
    }
}
