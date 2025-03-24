<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Nameprefix;

class NamePrefixRepository
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

        $query = Nameprefix::query();

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
        $nameprefixes = $query->paginate($perPage);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $nameprefixes, trans('message.data_fetch'));
    }
}
