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

class EcdialListFieldController extends Controller
{
    protected $ecdialListFieldRepository;

    public function __construct(EcdialListFieldsRepository $ecdialListFieldRepository)
    {
        $this->ecdialListFieldRepository = $ecdialListFieldRepository;
    }

    public function index(Request $request)
    {
        try {
            return $this->ecdialListFieldRepository->list($request);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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
        } catch (ValidationException $ex) {
            // If validation fails, this will automatically return validation errors
            return response()->json(['errors' => $ex->errors()], ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
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
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
