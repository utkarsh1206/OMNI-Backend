<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\State;
use App\Http\Response\CustomApiResponse;
use Exception;

class StateRepository
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $states = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $states,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in State List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
