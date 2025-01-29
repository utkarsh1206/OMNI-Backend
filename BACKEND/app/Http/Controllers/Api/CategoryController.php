<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            $name = $request->input('name');
            $department_id = $request->input('department_id');

            $query = Category::query();

            if ($name) {
                $query->where('name', $name);
            }
            if ($department_id) {
                $query->where('department_id', $department_id);
            }

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if ($page === 'all') {
                $category = $query->with('department')->get();
                return response()->json([
                    'data' => $category,
                ]);
            } else {
                $category = $query->with('department')->paginate($perPage);
                return response()->json([
                    'meta' => [
                        'current_page' => $category->currentPage(),
                        'last_page' => $category->lastPage(),
                        'per_page' => $category->perPage(),
                        'total' => $category->total(),
                    ],
                    'data' => $category->items(),
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories,name',
                'description' => 'nullable|string',
                'department_id' => 'required|exists:departments,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'department_id' => $request->department_id,
            ]);

            return response()->json([
                'message' => 'Category created successfully!',
                'data' => $category
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::with('department')->find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found.'
                ], 404);
            }

            return response()->json([
                'data' => $category
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories,name,' . $category->id, // Exclude current category
                'description' => 'nullable|string',
                'department_id' => 'required|exists:departments,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'department_id' => $request->department_id,
            ]);

            return response()->json([
                'message' => 'Category updated successfully!',
                'data' => $category
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error updating category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found.'
                ], 404);
            }

            $category->delete();

            return response()->json([
                'message' => 'Category deleted successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error deleting category: ' . $e->getMessage()
            ], 500);
        }
    }
}
