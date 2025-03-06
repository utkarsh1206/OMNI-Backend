<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\ProductRequest;
use App\Http\Response\CustomApiResponse;
use Exception;

// use Illuminate\Database\QueryException;

class ProductController extends Controller
{

    protected $ProductRepository;
    private $apiResponse;

    public function __construct(ProductRepository $ProductRepository, CustomApiResponse $customApiResponse)
    {
        $this->ProductRepository = $ProductRepository;
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        try {
            return $this->ProductRepository->list($request);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            return $this->ProductRepository->create($request);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($product)
    {
        try {
            $data = Product::findOrFail($product);
            return new ProductResource($data);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);                
        }
    }


     /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     description="Update the details of a specific product.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "status"},
     *             @OA\Property(property="name", type="string", example="Updated Product Name"),
     *             @OA\Property(property="description", type="string", example="Updated product description"),
     *             @OA\Property(property="status", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating product")
     *         )
     *     )
     * )
     */

    public function update(ProductRequest $request, $id)
    {
        try {
            return $this->ProductRepository->update($request, $id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     description="Delete a product by its ID.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting product")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            return $this->ProductRepository->delete($id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}