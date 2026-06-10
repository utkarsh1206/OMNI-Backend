<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EcdialListFieldRequest;
use App\Repositories\EcdialListFieldsRepository;
use App\Http\Resources\EcdialListFieldResource;
use App\Models\EcdialListField;
use Exception;
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
        return $this->ecdialListFieldRepository->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EcdialListFieldRequest $request)
    {
        return $this->ecdialListFieldRepository->create($request);
    }


    /**
     * Display the specified resource.
     */
    public function show($ecdialListField)
    {
        try {
            $listField = EcdialListField::findOrFail($ecdialListField);

            if (config('omni.FIELD_API_ENABLED')) {
                $listField->load(['apiConfig', 'dependencyMappings' => function ($q) use ($listField) {
                    $q->where('ecdial_list_field_dependencies.list_id', $listField->list_id);
                }]);
            }

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
        return $this->ecdialListFieldRepository->update($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->ecdialListFieldRepository->delete($id);
    }

    /**
     * Trigger the API configured for the given field and return the options response.
     *
     * Pass dependency_values as a key-value map for multi-parameter dependencies.
     * Legacy ?dependency_value=<value> is still accepted for single-dependency fields.
     */
    public function fetchOptions(Request $request, $id)
    {
        return $this->ecdialListFieldRepository->fetchOptions((int) $id, $request);
    }
}
