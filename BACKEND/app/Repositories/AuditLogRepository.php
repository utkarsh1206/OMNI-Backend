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
            if (!$request->filled('startDate') || !$request->filled('endDate')) {
                return response()->json([
                    'error' => trans("message.record_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $query = $this->buildAuditQuery($request)
                ->orderBy('created_at', 'desc');

            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);

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

    public function exportCsv(Request $request)
    {
        try {
            $selectedColumns = $request->input('selected_columns', []);

            if (empty($selectedColumns)) {
                return response()->json([
                    'error' => 'No columns selected'
                ]);
            }

            $query = $this->buildAuditQuery($request)
                ->orderBy('created_at', 'desc');

            $columns = [
                'UserName' => [
                    'value' => fn($log) =>
                    trim(optional($log->userInfo)->first_name . ' ' .
                        optional($log->userInfo)->last_name)
                ],

                'Remote IP' => [
                    'value' => fn($log) => $log->ipaddress
                ],

                'Access Time' => [
                    'value' => fn($log) =>
                    optional($log->created_at)->format('Y-m-d H:i:s')
                ],

                'Module' => [
                    'value' => fn($log) => $log->module_name
                ],

                'Action' => [
                    'value' => fn($log) => $log->action
                ],

                'Request Parameters' => [
                    'value' => fn($log) =>
                    json_encode(
                        json_decode($log->request_params, true),
                        JSON_UNESCAPED_UNICODE
                    )
                ],
            ];

            $validColumns = array_values(array_filter(
                $selectedColumns,
                fn($label) => isset($columns[$label])
            ));

            if (empty($validColumns)) {
                return response()->json([
                    'error' => 'No valid columns selected'
                ]);
            }

            // Headers are exactly what frontend sends (filtered)
            $headers = $validColumns;

            $url = exportCsvChunked(
                $query,
                $headers,
                function ($log) use ($validColumns, $columns) {
                    $row = [];

                    foreach ($validColumns as $label) {
                        $row[] = $columns[$label]['value']($log);
                    }

                    return $row;
                },
                'audit_log'
            );

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                ['file_url' => $url],
                trans('message.data_exported')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in exporting Audit log: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function buildAuditQuery(Request $request)
    {
        if (auth()->user()->id == 1) {
            $query = ActivityLog::with('userInfo');
        } else {
            $query = ActivityLog::with('userInfo')
                ->where('user_id', auth()->user()->id);
        }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
        }

        if ($request->filled('username')) {
            $query->whereHas('userInfo', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->username . '%')
                    ->orWhere('last_name', 'like', '%' . $request->username . '%');
            });
        }

        if ($request->filled('module_name')) {
            $query->where('module_name', 'like', '%' . $request->module_name . '%');
        }

        if ($request->filled('action')) {
            $query->where('request_method', 'like', '%' . $request->action . '%');
        }

        $query->whereNotNull('module_name')
            ->where('module_name', '!=', '');

        return $query;
    }
}
