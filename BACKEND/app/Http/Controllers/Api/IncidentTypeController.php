<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncidentType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class IncidentTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $name = $request->input('name', '');
            $priority = $request->input('priority', '');
            $department_id = $request->input('department_id');
            $category_id = $request->input('category_id');
            $sla_id = $request->input('sla_id');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = IncidentType::query();

            if ($name) {
                $query->where('name', $name);
            }
            if ($category_id) {
                $query->where('category_id', $category_id);
            }

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if (!empty($priority)) {
                $query->where('priority', $priority);  // Filter by priority
            }
            if (!empty($department_id)) {
                $query->where('$department_id', $department_id);  // Filter by priority
            }
            if (!empty($sla_id)) {
                $query->where('sla_id', $sla_id);  // Filter by priority
            }

            if ($page === 'all') {
                $incidentType = $query->with('category.department', 'sla_settings')->get();
                return response()->json([
                    'data' => $incidentType,
                ]);
            } else {
                $incidentType = $query->with('category.department', 'sla_settings')->paginate($perPage);
                return response()->json([
                    'meta' => [
                        'current_page' => $incidentType->currentPage(),
                        'last_page' => $incidentType->lastPage(),
                        'per_page' => $incidentType->perPage(),
                        'total' => $incidentType->total(),
                    ],
                    'data' => $incidentType->items(),
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching Incident Type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:incident_types,name',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'priority' => 'required|in:Normal,Low,Medium,High,Critical',
                'sla_id' => 'nullable|exists:sla_settings,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $incidentType = IncidentType::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'priority' => $request->priority,
                'sla_id' => $request->sla_id,
            ]);

            return response()->json([
                'message' => 'Incident Type created successfully!',
                'data' => $incidentType
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating Incident Type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $incidentType = IncidentType::with('category.department', 'sla_settings')->find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => 'Incident Type not found.'
                ], 404);
            }

            return response()->json([
                'data' => $incidentType
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching Incident Type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $incidentType = IncidentType::find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => 'Incident Type not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:incident_types,name,' . $incidentType->id, // Exclude current category
                'description' => 'nullable|string',
                'category_id' => 'required|exists:categories,id',
                'priority' => 'required|in:normal,low,medium,high,critical',
                'sla_id' => 'nullable|exists:sla_settings,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $incidentType->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'priority' => $request->priority,
                'sla_id' => $request->sla_id,
            ]);

            return response()->json([
                'message' => 'Incident Type updated successfully!',
                'data' => $incidentType
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error updating Incident Type: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $incidentType = IncidentType::find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => 'Incident Type not found.'
                ], 404);
            }

            $incidentType->delete();

            return response()->json([
                'message' => 'Incident Type deleted successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error deleting Incident Type: ' . $e->getMessage()
            ], 500);
        }
    }
}
