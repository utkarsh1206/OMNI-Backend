<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\IndustryType;
use App\Http\Response\CustomApiResponse;
use Illuminate\Database\QueryException;

class IndustryTypeRepository
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

            $query = IndustryType::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                        ->orWhere('status', 'like', '%' . $searchTerm . '%');
                });
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $industry_type = $page == config('omni.ALL_PAGE') ? $query->with('category.department', 'slaSettings')->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $industry_type, trans('message.data_fetch'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
