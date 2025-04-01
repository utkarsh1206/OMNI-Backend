<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\BusinessType;
use App\Http\Response\CustomApiResponse;
use Exception;

class BusinessTypeRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');

            $query = BusinessType::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('status', 'like', '%' . $searchTerm . '%');
                });
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                // If 'page' is 'all', retrieve all records without pagination
                $business_type = $query->get();
            } else {
                // Paginate the results
                $business_type = $query->paginate($perPage);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $business_type, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Business Type Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
