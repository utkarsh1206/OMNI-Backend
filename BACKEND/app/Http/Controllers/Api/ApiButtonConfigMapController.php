<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiButtonConfigMapRequest;
use App\Repositories\ApiButtonConfigMapRepository;

class ApiButtonConfigMapController extends Controller
{
    private $repository;

    public function __construct(ApiButtonConfigMapRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(ApiButtonConfigMapRequest $request)
    {
        return $this->repository->store($request->validated());
    }

    public function show(string $buttonKey)
    {
        return $this->repository->getByButton($buttonKey);
    }

    public function destroy(string $buttonKey)
    {
        return $this->repository->clearByButton($buttonKey);
    }
}
