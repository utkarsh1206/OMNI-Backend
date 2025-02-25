<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\BusinessType;
use App\Http\Response\CustomApiResponse;


class BusinessTypeRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = BusinessType::query();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $business_type = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $business_type, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $business_type = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $business_type, trans('message.data_fetch'));
        }
    }
}
