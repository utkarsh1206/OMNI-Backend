<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $name = $request->input('name', '');
            $status = $request->input('status', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Product::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }

            if (!empty($status)) {
                $query->where('status', $status === 'true');
            }

            if ($page === 'all') {
                $products = $query->get();
                return response()->json([
                    'data' => $products,
                ]);
            } else {
                $products = $query->paginate($perPage);
                return response()->json([
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                    'data' => $products->items(),
                ]);
            }
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching products: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:products,name',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Product created successfully!',
                'data' => $product
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'data' => $product
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:products,name,' . $product->id,
                'description' => 'nullable|string',
                'status' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Product updated successfully!',
                'data' => $product
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found.'
                ], 404);
            }

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }
}