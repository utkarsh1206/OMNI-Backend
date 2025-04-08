<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Response\CustomApiResponse;
use Exception;

class ProductController extends Controller
{

    protected $productRepository;

    private $apiResponse;

    public function __construct(ProductRepository $productRepository, CustomApiResponse $customApiResponse)
    {
        $this->productRepository = $productRepository;
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Get(
     *  path="/api/products",
     * operationId="getProductsList",
     * tags={"Products"},
     * summary="Get list of products",
     * description="Returns list of products",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Page number",
     * required=false,
     * @OA\Schema(
     * type="integer",
     * default=1
     * )
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Number of items per page",
     * required=false,
     * @OA\Schema(
     * type="integer",
     * default=10
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     * @OA\Property(
     * property="data",
     * type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="p1"),
     * @OA\Property(property="description", type="string", example="p3"),
     * @OA\Property(property="status", type="boolean", example=true),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z")
     * )
     * ),
     * @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/products?page=1"),
     * @OA\Property(property="from", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=2),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/products?page=2"),
     * @OA\Property(
     * property="links",
     * type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="url", type="string", nullable=true, example=null),
     * @OA\Property(property="label", type="string", example="&laquo; Previous"),
     * @OA\Property(property="active", type="boolean", example=false)
     * )
     * ),
     * @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/products?page=2"),
     * @OA\Property(property="path", type="string", example="http://localhost:8000/api/products"),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="to", type="integer", example=10),
     * @OA\Property(property="total", type="integer", example=11)
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * )
     * )
     */
    public function index(Request $request)
    {
        return $this->productRepository->list($request);
    }

    /**
     * @OA\Post(
     * path="/api/products",
     * operationId="storeProduct",
     * tags={"Products"},
     * summary="Store new product",
     * description="Creates a new product",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Product data",
     *    @OA\JsonContent(
     *       required={"name","description","status"},
     *       @OA\Property(property="name", type="string", example="Product Name"),
     *       @OA\Property(property="description", type="string", example="Product Description"),
     *       @OA\Property(property="status", type="boolean", example=true)
     *    ),
     * ),
     * @OA\Response(
     *    response=201,
     *    description="Product created successfully",
     *    @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Product created successfully"),
     *       @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="name", type="string", example="Product Name"),
     *          @OA\Property(property="description", type="string", example="Product Description"),
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     *          @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z")
     *       )
     *    )
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Bad Request"
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Validation Error"
     * ),
     * @OA\Response(
     *    response=500,
     *    description="Server Error"
     * )
     * )
     */
    public function store(ProductRequest $request)
    {
        return $this->productRepository->create($request);
    }

    /**
     * @OA\Get(
     * path="/api/products/{product}",
     * operationId="getProductById",
     * tags={"Products"},
     * summary="Get product information",
     * description="Returns product data",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    name="product",
     *    description="Product id",
     *    required=true,
     *    in="path",
     *    @OA\Schema(
     *       type="integer"
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Successful operation",
     *    @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="data", type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="name", type="string", example="Product Name"),
     *          @OA\Property(property="description", type="string", example="Product Description"),
     *          @OA\Property(property="status", type="boolean", example=true),
     *          @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     *          @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z")
     *       )
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Product not found"
     * ),
     * @OA\Response(
     *    response=500,
     *    description="Server Error"
     * )
     * )
     */
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
     * path="/api/products/{id}",
     * operationId="updateProduct",
     * tags={"Products"},
     * summary="Update existing product",
     * description="Updates a product and returns updated data",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    name="id",
     *    description="Product id",
     *    required=true,
     *    in="path",
     *    @OA\Schema(
     *       type="integer"
     *    )
     * ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Product data",
     *    @OA\JsonContent(
     *       @OA\Property(property="name", type="string", example="Updated Product Name"),
     *       @OA\Property(property="description", type="string", example="Updated Product Description"),
     *       @OA\Property(property="status", type="boolean", example=false)
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Product updated successfully",
     *    @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Product updated successfully"),
     *       @OA\Property(
     *          property="data",
     *          type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *          @OA\Property(property="name", type="string", example="Updated Product Name"),
     *          @OA\Property(property="description", type="string", example="Updated Product Description"),
     *          @OA\Property(property="status", type="boolean", example=false),
     *          @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     *          @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-01T10:22:15.000000Z")
     *       )
     *    )
     * ),
     * @OA\Response(
     *    response=400,
     *    description="Bad Request"
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Product not found"
     * ),
     * @OA\Response(
     *    response=422,
     *    description="Validation Error"
     * ),
     * @OA\Response(
     *    response=500,
     *    description="Server Error"
     * )
     * )
     */
    public function update(Request $request, $id)
    {
        return $this->productRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     * path="/api/products/{id}",
     * operationId="deleteProduct",
     * tags={"Products"},
     * summary="Delete existing product",
     * description="Deletes a product and returns success message",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     *    name="id",
     *    description="Product id",
     *    required=true,
     *    in="path",
     *    @OA\Schema(
     *       type="integer"
     *    )
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Product deleted successfully",
     *    @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string", example="Product deleted successfully")
     *    )
     * ),
     * @OA\Response(
     *    response=401,
     *    description="Unauthorized"
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Product not found"
     * ),
     * @OA\Response(
     *    response=500,
     *    description="Server Error"
     * )
     * )
     */
    public function destroy($id)
    {
        return $this->productRepository->delete($id);
    }
}
