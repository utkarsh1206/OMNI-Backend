<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\ActivityLog;



class AuditLogRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        // Check if the authenticated user's user_id is 0 or not
        if (auth()->user()->id == 1) {
            // If user_id is 0, fetch all records
            $query = ActivityLog::query();
        } else {
            // Otherwise, fetch only the authenticated user's data
            $query = ActivityLog::where('user_id', auth()->user()->id);
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $result = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $result, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $result = $query->orderBy('created_at', 'desc')->paginate($perPage);
            // Return the response with pagination meta
            return $this->apiResponse->getResponseStructure(trans('message.success'), $result, trans('message.data_fetch'));
        }
    }

    public function view($info)
    {
        return $this->apiResponse->getResponseStructure(trans('message.success'), $info, trans('message.data_fetch'));
    }
}
