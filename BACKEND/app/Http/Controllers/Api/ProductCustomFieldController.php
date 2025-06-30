<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductCustomFieldRequest;
use App\Http\Response\CustomApiResponse;
use App\Repositories\ProductCustomFieldsRepository;

class ProductCustomFieldController extends Controller
{
    protected $repository;
    private $apiResponse;

    public function __construct(ProductCustomFieldsRepository $repository, CustomApiResponse $customApiResponse)
    {
        $this->repository = $repository;
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        return $this->repository->list($request);
    }

    public function store(ProductCustomFieldRequest $request)
    {
        return $this->repository->create($request);
    }

    public function show($id)
    {
        try {
            return $this->repository->show($id);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(Request $request, $id)
    {
        return $this->repository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }
}
