<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $name = $request->input('name', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Department::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }

            if ($page === 'all') {
                $departments = $query->get();
                return response()->json([
                    'data' => $departments,
                ]);
            } else {
                $departments = $query->paginate($perPage);
                return response()->json([
                    'meta' => [
                        'current_page' => $departments->currentPage(),
                        'last_page' => $departments->lastPage(),
                        'per_page' => $departments->perPage(),
                        'total' => $departments->total(),
                    ],
                    'data' => $departments->items(),
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching departments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:departments,name',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Department created successfully!',
                'data' => $department
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' => 'Department not found.'
                ], 404);
            }

            return response()->json([
                'data' => $department
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' => 'Department not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:departments,name,' . $department->id, // Exclude current department
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $department->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Department updated successfully!',
                'data' => $department
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error updating department: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' => 'Department not found.'
                ], 404);
            }

            $department->delete();

            return response()->json([
                'message' => 'Department deleted successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error deleting department: ' . $e->getMessage()
            ], 500);
        }
    }
}
