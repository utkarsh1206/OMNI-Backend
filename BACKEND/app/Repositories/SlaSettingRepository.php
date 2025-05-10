<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use App\Models\SlaSetting;
use App\Models\SlaOffDay;
use App\Models\SlaHoliday;
use App\Models\SlaTarget;
use Illuminate\Support\Facades\DB;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class SlaSettingRepository
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
            $query = SlaSetting::query()->with(['offDays', 'holidays', 'targets', 'targets.priority']);

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%$searchTerm%")
                        ->orWhere('start_time', 'like', "%$searchTerm%")
                        ->orWhere('end_time', 'like', "%$searchTerm%");
                });
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                $slas = $query->get();
                // $slasFormatted = $slas->map(function ($sla) {
                //     return $this->formatSlaResponse($sla);
                // });
            } else {
                $slas = $query->paginate($perPage);
                // $slasFormatted = $slas->items();

                // $slasFormatted = collect($slasFormatted)->map(function ($sla) {
                //     return $this->formatSlaResponse($sla);
                // });
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $slas, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching SLA settings: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->all();

            DB::beginTransaction();

            $sla = SlaSetting::create($validated['sla']);
            $this->saveRelatedData($sla, $validated);

            DB::commit();

            $response = $this->formatSlaResponse($sla->load(['offDays', 'holidays', 'targets', 'targets.priority']));

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.sla_setting_create_msg'));
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error occurred while creating SLA:  ', $ex->getMessage());
            if ($ex->getCode() === '23000' && strpos($ex->getMessage(), '1062') !== false) {
                return response()->json(['message' => 'Error: SLA name must be unique.'], ResponseHTTP::HTTP_BAD_REQUEST);
            }
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show(SlaSetting $slaSetting)
    {
        try {
            $response = $this->formatSlaResponse($slaSetting->load(['offDays', 'holidays', 'targets', 'targets.priority']));

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching SLA setting: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, SlaSetting $slaSetting)
    {
        try {
            $validated = $request->all();

            DB::beginTransaction();

            // Update the SLA setting
            $slaSetting->update($validated['sla']);


            // Remove existing related data and save new data
            $slaSetting->offDays()->delete();
            $slaSetting->holidays()->delete();
            $slaSetting->targets()->delete();

            $this->saveRelatedData($slaSetting, $validated);

            DB::commit();

            $response = $this->formatSlaResponse($slaSetting->load(['offDays', 'holidays', 'targets', 'targets.priority']));

            return $this->apiResponse->getResponseStructure(trans('message.success'), $response, trans('message.sla_setting_update_msg'));
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error during SLA update: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($slaSetting)
    {
        try {
            $slaSetting->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $slaSetting, trans('message.sla_setting_delete_msg'));
        } catch (\Exception $ex) {
            generateLogs('error', 'Error deleting SLA setting: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function saveRelatedData(SlaSetting $sla, $validated)
    {
        // Save Off-Days
        if (isset($validated['off_days'])) {
            foreach ($validated['off_days'] as $day) {
                SlaOffDay::create([
                    'sla_id' => $sla->id,
                    'day' => $day,
                ]);
            }
        }

        // Save Holidays
        if (isset($validated['holidays'])) {
            foreach ($validated['holidays'] as $holiday) {
                SlaHoliday::create([
                    'sla_id' => $sla->id,
                    'holiday_date' => $holiday['holiday_date'],
                    'description' => $holiday['description'] ?? null,
                ]);
            }
        }

        // Save Targets
        if (isset($validated['targets'])) {
            foreach ($validated['targets'] as $escalationLevel => $targetGroup) {
                foreach ($targetGroup as $target) {
                    SlaTarget::create([
                        'sla_id' => $sla->id,
                        'escalation_level' => $escalationLevel,
                        'priority_type' => $target['priority_type'],
                        'response_time_value' => $target['response_time_value'],
                        'alert_before_response_value' => $target['alert_before_response_value'],
                        'resolve_time_value' => $target['resolve_time_value'],
                        'alert_before_resolve_value' => $target['alert_before_resolve_value'],
                    ]);
                }
            }
        }
    }

    private function formatSlaResponse(SlaSetting $slaSetting)
    {
        $offDays = $slaSetting->offDays->pluck('day')->toArray();
        $holidays = $slaSetting->holidays->map(function ($holiday) {
            return [
                'holiday_date' => $holiday->holiday_date,
                'description' => $holiday->description
            ];
        })->toArray();

        $targets = [];
        foreach ($slaSetting->targets as $target) {
            $targets[$target->escalation_level][] = [
                'priority_type' => $target->priority_type ? $target->priority->name : null,
                'response_time_value' => $target->response_time_value,
                'alert_before_response_value' => $target->alert_before_response_value,
                'resolve_time_value' => $target->resolve_time_value,
                'alert_before_resolve_value' => $target->alert_before_resolve_value,
            ];
        }

        return [
            'id' => $slaSetting->id,
            'name' => $slaSetting->name,
            'is_24_hours' => $slaSetting->is_24_hours,
            'start_time' => $slaSetting->start_time,
            'end_time' => $slaSetting->end_time,
            'description' => $slaSetting->description,
            'off_days' => $offDays,
            'holidays' => $holidays,
            'targets' => $targets,
        ];
    }
}
