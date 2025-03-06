<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EcdialListFieldRequest;
use App\Repositories\EcdialListFieldsRepository;
use App\Http\Resources\EcdialListFieldResource;
use App\Models\EcdialListField;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Response\CustomApiResponse;

class EcdialListFieldController extends Controller
{
    protected $ecdialListFieldRepository;
    private $apiResponse;

    public function __construct(EcdialListFieldsRepository $ecdialListFieldRepository, CustomApiResponse $customApiResponse)
    {
        $this->ecdialListFieldRepository = $ecdialListFieldRepository;
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        try {
            return $this->ecdialListFieldRepository->list($request);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EcdialListFieldRequest $request)
    {
        try {
            // Validation is automatically done by EcdialListFieldRequest
            return $this->ecdialListFieldRepository->create($request);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($ecdialListField)
    {
        try {
            $listField = EcdialListField::findOrFail($ecdialListField);
            return new EcdialListFieldResource($listField);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EcdialListFieldRequest $request, $id)
    {
        try {
            return $this->ecdialListFieldRepository->update($request, $id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            return $this->ecdialListFieldRepository->delete($id);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
