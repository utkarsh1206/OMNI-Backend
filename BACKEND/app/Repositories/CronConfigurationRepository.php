<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronConfiguration;
use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class CronConfigurationRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Filter Conditions
        $commandSignature = $request->input('command_signature');
        $scheduleType = $request->input('schedule_type');
        $status = $request->input('status');

        // Start the query for tenants
        $query = CronConfiguration::select("id", "command_signature", "schedule_type", "schedule_expression");

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('command_signature', 'like', '%' . $searchTerm . '%')
                    ->orwhere('schedule_type', 'like', '%' . $searchTerm . '%')
                    ->orwhere('schedule_expression', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($commandSignature) {
            $query->where('command_signature', $commandSignature);
        }

        if ($scheduleType) {
            $query->where('schedule_type', $scheduleType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($page === 'all') {
            $cronConfiguration = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $cronConfiguration = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.data_fetch'));
        }
    }

    public function store($request)
    {
        try {
            $cronConfiguration = CronConfiguration::create([
                'command_signature' => $request->command_signature,
                'schedule_type' => $request->schedule_type,
                'schedule_expression' => $request->schedule_expression,
                'status' => isset($request->status) ? $request->status : "active"
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.data_fetch'));
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => trans('message.wrong_with_system'), 'error' => $e->getMessage()], 500);
        }
    }


    public function update($request, $id)
    {
        try {
            $cronConfiguration = CronConfiguration::find($id);

            if (!$cronConfiguration) {
                return response()->json([
                    'message' => trans("message.cron_configuration_not_found"),
                ], 404);
            }
            $cronConfiguration->update($request->all());
            return $this->apiResponse->getResponseStructure(trans('message.cron_configuration_update'), $cronConfiguration, trans('message.data_fetch'));
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Internal Server Error', 'error' => $e->getMessage()], 500);
        }
    }


    public function delete($id)
    {
        try {
            $cronConfiguration = CronConfiguration::find($id);
            if (!$cronConfiguration) {
                return response()->json([
                    'message' => trans("message.cron_configuration_not_found"),
                ], 404);
            }
            $cronConfiguration->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.cron_configuration_delete'));
        } catch (Exception $e) {
            Log::error('Error while generating the cron configuration', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => trans("message.wrong_with_system"), 'error' => $e->getMessage()], 500);
        }
    }
}
