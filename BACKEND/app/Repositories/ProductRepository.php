<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\Product;
use Illuminate\Database\QueryException;


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
            } else {
                $products = $query->paginate($perPage);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $products, trans('message.fetch_data'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {

        try {
            
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $product, trans('message.product_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'message' => trans("message.product_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $product, trans('message.product_updated'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'message' => trans("message.product_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            $product->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $product, trans('message.product_deleted'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'message' => trans("message.product_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $product, trans('message.fetch_data'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
