<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronConfiguration;
use App\Http\Response\CustomApiResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class CronConfigurationRepository
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
            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

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

            $cronConfiguration = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Cron configuraion Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating Cron Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update($request, $id)
    {
        try {
            $cronConfiguration = CronConfiguration::find($id);

            if (!$cronConfiguration) {
                return response()->json([
                    'message' => trans("message.cron_configuration_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            $cronConfiguration->update($request->all());
            return $this->apiResponse->getResponseStructure(trans('message.cron_configuration_update'), $cronConfiguration, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Cron Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $cronConfiguration = CronConfiguration::find($id);
            if (!$cronConfiguration) {
                return response()->json([
                    'message' => trans("message.cron_configuration_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            $cronConfiguration->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $cronConfiguration, trans('message.cron_configuration_delete'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Cron Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
