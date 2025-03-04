<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

class ProductRepository{

    private $apiResponce;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $filters = [
            'search' => 'searchTerm',
            'name' => 'name',
            'description' => 'description',
            'status' => 'status',
        ];

        $inputs = [];
        foreach ($filters as $key => $variable) {
            $inputs[$variable] = $request->input($key, '');
        }

        $perPage = $request->input('per_page', 10); 
        $page = $request->input('page', 1);         
    
        $perPage = (int)$perPage;

        
        $query = Product::query();

        if (!empty($inputs['searchTerm'])) {
            $query->where(function ($q) use ($inputs) {
                $searchTerm = $inputs['searchTerm'];
                $q->where('name', 'like', "%" . $searchTerm . "%")
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        foreach ($filters as $key => $variable) {
            if (!empty($inputs[$variable]) && $variable !== 'searchTerm') {
                if ($variable === 'status') {

                    $status = filter_var($inputs[$variable], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if (!is_null($status)) {
                        $query->where($variable, $status);
                    }
                } else {
                    $query->where($variable, $inputs[$variable]);
                }
            }
        }

        if ($page === 'all') {
            $products = $query->get(); 
        } else {
            $products = $query->paginate($perPage); 
        }

        
        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            $products, 
            trans('message.data_fetch')
        );
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
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
