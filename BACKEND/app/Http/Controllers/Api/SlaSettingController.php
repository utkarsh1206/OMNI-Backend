<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlaSettingResource;
use Illuminate\Http\Request;
use App\Models\SlaSetting;
use App\Models\SlaOffDay;
use App\Models\SlaHoliday;
use App\Models\SlaTarget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SlaSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $query = SlaSetting::query()->with(['offDays', 'holidays', 'targets']);

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%$searchTerm%")
                        ->orWhere('start_time', 'like', "%$searchTerm%")
                        ->orWhere('end_time', 'like', "%$searchTerm%");
                });
            }

            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            if ($page === 'all') {
                // If 'all' is passed, return all records without pagination
                $slas = $query->get();
                return response()->json([
                    'data' => $slas,
                ]);
            } else {
                // Paginate the results
                $slas = $query->paginate($perPage);

                return response()->json([
                    'meta' => [
                        'current_page' => $slas->currentPage(),
                        'last_page' => $slas->lastPage(),
                        'per_page' => $slas->perPage(),
                        'total' => $slas->total(),
                    ],
                    'data' => $slas->items(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching SLA settings: " . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching SLA settings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validateRequest($request);

            DB::beginTransaction();

            $sla = SlaSetting::create($validated['sla']);
            $this->saveRelatedData($sla, $validated);

            DB::commit();
            return response()->json([
                'message' => 'SLA created successfully',
                'data' => new SlaSettingResource($sla->load(['offDays', 'holidays', 'targets']))
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("Validation failed while creating SLA: ", ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error occurred while creating SLA: " . $e->getMessage());
            // Handle specific database error like duplicate entry
            if ($e->getCode() === '23000' && strpos($e->getMessage(), '1062') !== false) {
                return response()->json(['message' => 'Error: SLA name must be unique.'], 400);
            }
            return response()->json([
                'message' => 'Error occurred while creating SLA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(SlaSetting $slaSetting)
    {
        try {
            return response()->json($slaSetting->load(['offDays', 'holidays', 'targets']), 200);
        } catch (\Exception $e) {
            Log::error("Error fetching SLA setting: " . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching SLA setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info("SLA update request received for ID: {$id}");

            $validated = $this->validateRequest($request);
            Log::info("Validated data: ", $validated);

            DB::beginTransaction();

            // Find the SLA by ID
            $slaSetting = SlaSetting::find($id);

            // Check if SLA setting exists
            if (!$slaSetting) {
                Log::error("SLA not found for ID: {$id}");
                return response()->json(['message' => 'SLA setting not found'], 404);
            }

            // Update the SLA setting
            $slaSetting->update($validated['sla']);
            Log::info("SLA updated successfully: ", $slaSetting->toArray());

            // Remove existing related data and save new data
            $slaSetting->offDays()->delete();
            $slaSetting->holidays()->delete();
            $slaSetting->targets()->delete();

            $this->saveRelatedData($slaSetting, $validated);

            DB::commit();
            Log::info("Transaction committed, SLA data after update: ", $slaSetting->load(['offDays', 'holidays', 'targets'])->toArray());

            return response()->json([
                'message' => 'SLA updated successfully',
                'data' => new SlaSettingResource($slaSetting->load(['offDays', 'holidays', 'targets']))
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error("Validation failed during SLA update: ", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error during SLA update: " . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while updating SLA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(SlaSetting $slaSetting)
    {
        try {
            if (!$slaSetting) {
                return response()->json(['message' => 'SLA setting not found'], 404);
            }

            $slaSetting->delete();
            return response()->json(['message' => 'SLA deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting SLA setting: " . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while deleting SLA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function validateRequest($request)
    {
        try {
            $validated = $request->validate([
                'sla.name' => 'required|string|max:100',
                'sla.description' => 'nullable|string|max:255',
                'sla.is_24_hours' => 'required|boolean',
                'sla.start_time' => 'nullable|date_format:H:i',
                'sla.end_time' => 'nullable|date_format:H:i',

                'off_days' => 'required|array',
                'off_days.*' => 'required|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',

                'holidays' => 'nullable|array',
                'holidays.*.holiday_date' => 'required_if:holidays.*,.*|date', // Only required if holidays are provided
                'holidays.*.description' => 'nullable|string|max:255',

                'targets' => 'required|array',
                'targets.*.priority_type' => 'required|string|in:Critical,High,Medium,Low',
                'targets.*.response_time_value' => 'required|integer|min:1',
                'targets.*.response_time_unit' => 'required|string|in:Minute,Hour',
                'targets.*.alert_before_response_value' => 'required|integer|min:1',
                'targets.*.alert_before_response_unit' => 'required|string|in:Minute,Hour',
                'targets.*.resolve_time_value' => 'required|integer|min:1',
                'targets.*.resolve_time_unit' => 'required|string|in:Minute,Hour',
                'targets.*.alert_before_resolve_value' => 'required|integer|min:1',
                'targets.*.alert_before_resolve_unit' => 'required|string|in:Minute,Hour',
            ]);

            return $validated;
        } catch (ValidationException $e) {
            Log::error("Validation failed with errors: ", $e->errors());

            throw $e;
        }
    }

    private function saveRelatedData(SlaSetting $sla, $validated)
    {
        // Save Off-Days
        foreach ($validated['off_days'] as $day) {
            SlaOffDay::create([
                'sla_id' => $sla->id,
                'day' => $day,
            ]);
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
        foreach ($validated['targets'] as $target) {
            SlaTarget::create([
                'sla_id' => $sla->id,
                'priority_type' => $target['priority_type'],
                'response_time_value' => $target['response_time_value'],
                'response_time_unit' => $target['response_time_unit'],
                'alert_before_response_value' => $target['alert_before_response_value'],
                'alert_before_response_unit' => $target['alert_before_response_unit'],
                'resolve_time_value' => $target['resolve_time_value'],
                'resolve_time_unit' => $target['resolve_time_unit'],
                'alert_before_resolve_value' => $target['alert_before_resolve_value'],
                'alert_before_resolve_unit' => $target['alert_before_resolve_unit'],
            ]);
        }
    }
}
