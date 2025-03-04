<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ProductRepository;
use App\Http\Requests\ProductRequest;
use App\Http\Response\CustomApiResponse;

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

    public function update(Request $request, $id)
    {
        try {
            return $this->ProductRepository->update($request, $id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function destroy($id)
    {
        try {
            return $this->ProductRepository->delete($id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}