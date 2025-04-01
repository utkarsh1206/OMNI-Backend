<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\ActivityLog;
use Exception;

class AuditLogRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            // Check if the authenticated user's user_id is 0 or not
            if (auth()->user()->id == 1) {
                // If user_id is 0, fetch all records
                $query = ActivityLog::query();
            } else {
                // Otherwise, fetch only the authenticated user's data
                $query = ActivityLog::where('user_id', auth()->user()->id);
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                // If 'page' is 'all', retrieve all records without pagination
                $result = $query->get();
            } else {
                // Paginate the results
                $result = $query->orderBy('created_at', 'desc')->paginate($perPage);
            }
            // Return the response with pagination meta
            return $this->apiResponse->getResponseStructure(trans('message.success'), $result, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Audit logs Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function view($info)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $info, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Audit log: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
