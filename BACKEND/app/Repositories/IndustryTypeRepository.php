<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\IndustryType;
use App\Http\Response\CustomApiResponse;


class IndustryTypeRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = IndustryType::query();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%');
            });
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            $industry_type = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $industry_type, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $industry_type = $query->paginate($perPage);
            // Return the response with pagination meta
            return $this->apiResponse->getResponseStructure(trans('message.success'), $industry_type, trans('message.data_fetch'));
        }

    }

   

}
