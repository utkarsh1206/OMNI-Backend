<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\City;
use App\Http\Response\CustomApiResponse;

class CityRepository
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
        $state_id = $request->input('state_id', '');

        $query = City::query()->where('state_id', $state_id);

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
            $cities = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cities, trans('message.data_fetch'));
        } else {
            $cities = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cities, trans('message.data_fetch'));
        }
    }
}
