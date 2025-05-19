<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Exception;
use App\Http\Response\CustomApiResponse;

class ProductRepository
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

            // Advanced filters for Product
            $filters = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => $request->input('status'),
            ];

            $query = Product::query();

            // ✅ LIVE SEARCH — searches across multiple text fields
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // ✅ ADVANCED FILTERS — applies only if specific filter field is given
            if (!empty($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (!empty($filters['description'])) {
                $query->where('description', 'like', "%{$filters['description']}%");
            }


            if (!empty($filters['status'])) {
                $status = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!is_null($status)) {
                    $query->where('status', $status);
                }
            }

            // ✅ PAGINATION
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $products = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $products,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Product List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function create(Request $request)
    {
        try {
            $status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $product = Product::create([
                'name' => $request->name,
                "description" => $request->description,
                "status" => $status
            ]);

            $data = new ProductResource($product);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.product_create_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Product List : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $status = filter_var($request->status, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $product = Product::findOrFail($id);

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $status
            ]);
            $data = new ProductResource($product);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.product_update_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Product Update : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::where('id', $id)->firstOrFail();

            $product->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $id, trans('message.product_delete_success'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Product Delete : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
