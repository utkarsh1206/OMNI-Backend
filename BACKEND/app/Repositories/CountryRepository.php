<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Http\Response\CustomApiResponse;
use Exception;

class CountryRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            // Get the search term from the request
            $searchTerm = $request->input('search', '');

            $query = Country::query();

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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                $countires = $query->get();
            } else {
                // Paginate the results
                $countires = $query->paginate($perPage);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $countires, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Country Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
