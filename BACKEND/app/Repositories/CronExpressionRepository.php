<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronExpression;
use App\Http\Response\CustomApiResponse;
use Exception;
class CronExpressionRepository
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

            $query = CronExpression::select("id", "schedule_type", "schedule_expression");

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('schedule_type', 'like', '%' . $searchTerm . '%');
                });
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                $expressions = $query->get();
            } else {
                // Paginate the results
                $expressions = $query->paginate($perPage);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $expressions, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Cron Expression Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
