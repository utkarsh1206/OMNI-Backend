<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\EcdialListRequest;
use App\Repositories\EcdialListRepository;
use App\Http\Resources\EcdialListResource;
use App\Models\EcdialLists;
use Exception;
use App\Http\Response\CustomApiResponse;

class EcdialListsController extends Controller
{
    protected $ecdialListRepository;
    private $apiResponse;

    public function __construct(EcdialListRepository $ecdialListRepository, CustomApiResponse $customApiResponse)
    {
        $this->ecdialListRepository = $ecdialListRepository;
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        return $this->ecdialListRepository->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EcdialListRequest $request)
    {
        return $this->ecdialListRepository->create($request);
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
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EcdialListRequest $request, $list_id)
    {
        return $this->ecdialListRepository->update($request, $list_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($list_id)
    {
        return $this->ecdialListRepository->delete($list_id);
    }
}
