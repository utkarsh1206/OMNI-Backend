<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\ActivityLog;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

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

            if (!$request->filled('startDate') || !$request->filled('endDate')) {
                return response()->json([
                    'error' => trans("message.record_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);

            if ($request->filled('username')) {
                $query->whereHas('userInfo', function($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->username . '%')
                      ->orWhere('last_name', 'like', '%' . $request->username . '%');
                });
            }

            // Optional filter: module
            if ($request->filled('module_name')) {
                $query->where('action', 'like', '%' . $request->module_name . '%');
            }

            // Optional filter: action
            if ($request->filled('action')) {
                $query->where('request_method', 'like', '%' . $request->action . '%');
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $result = $page === config('omni.ALL_PAGE') ? $query->get() : $query->orderBy('created_at', 'desc')->paginate($perPage);
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
