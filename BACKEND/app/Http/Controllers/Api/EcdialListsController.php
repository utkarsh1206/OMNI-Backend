<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EcdialListRequest;
use App\Repositories\EcdialListRepository;
use App\Http\Resources\EcdialListResource;
use App\Models\EcdialLists;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class EcdialListsController extends Controller
{
    protected $ecdialListRepository;

    public function __construct(EcdialListRepository $ecdialListRepository)
    {
        $this->ecdialListRepository = $ecdialListRepository;
    }

    public function index(Request $request)
    {
        try {
            return $this->ecdialListRepository->list($request);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EcdialListRequest $request)
    {
        try {
            return $this->ecdialListRepository->create($request);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($ecdialLists)
    {
        try {
            $list = EcdialLists::findOrFail($ecdialLists);
            return new EcdialListResource($list);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EcdialListRequest $request, $list_id)
    {
        try {
            return $this->ecdialListRepository->update($request, $list_id);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($list_id)
    {
        try {
            return $this->ecdialListRepository->delete($list_id);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.wrong_with_system')], ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
