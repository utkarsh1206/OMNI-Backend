<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronExpression;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class CronExpressionRepository
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

         $query = CronExpression::select("id", "schedule_type", "schedule_expression");
 
         // If there's a search term, apply it to the query
         if (!empty($searchTerm)) {
             $query->where(function ($q) use ($searchTerm) {
                 $q->where('schedule_type', 'like', '%' . $searchTerm . '%');
             });
         }
 
         // Paginate the results
         $perPage = $request->input('per_page', 10);
         $page = $request->input('page', 1);

         if ($page === 'all') {
             // If 'page' is 'all', retrieve all records without pagination
             $expressions = $query->get();
             return $this->apiResponse->getResponseStructure(trans('message.success'), $expressions, trans('message.data_fetch'));
         } else {
             // Paginate the results
             $expressions = $query->paginate($perPage);
             // Return the response with pagination meta
             return $this->apiResponse->getResponseStructure(trans('message.success'), $expressions, trans('message.data_fetch'));
         }
    }

 
}
