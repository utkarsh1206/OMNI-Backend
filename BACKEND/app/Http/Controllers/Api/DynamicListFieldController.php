<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DynamicListFieldRequest;
use App\Repositories\DynamicListFieldsRepository;
use App\Http\Resources\DynamicListFieldResource;
use App\Models\DynamicListField;
use Exception;
use App\Http\Response\CustomApiResponse;

class DynamicListFieldController extends Controller
{
    protected $dynamicListFieldRepository;

    private $apiResponse;

    public function __construct(DynamicListFieldsRepository $dynamicListFieldRepository, CustomApiResponse $customApiResponse)
    {
        $this->dynamicListFieldRepository = $dynamicListFieldRepository;
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        return $this->dynamicListFieldRepository->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DynamicListFieldRequest $request)
    {
        return $this->dynamicListFieldRepository->create($request);
    }


    /**
     * Display the specified resource.
     */
    public function show($ecdialListField)
    {
        try {
            $listField = DynamicListField::findOrFail($ecdialListField);
            return new DynamicListFieldResource($listField);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DynamicListFieldRequest $request, $id)
    {
        return $this->dynamicListFieldRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->dynamicListFieldRepository->delete($id);
    }
}
