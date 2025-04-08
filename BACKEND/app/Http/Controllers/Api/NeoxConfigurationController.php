<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\NeoxConfigurationRequest;
use App\Repositories\NeoxConfigurationRepository;
use App\Http\Resources\NeoxConfigurationResource;
use App\Models\NeoxConfiguration;

class NeoxConfigurationController extends Controller
{
    protected $neoxConfigurationRepository;

    public function __construct(NeoxConfigurationRepository $neoxConfigurationRepository)
    {
        $this->neoxConfigurationRepository = $neoxConfigurationRepository;
    }

    public function index(Request $request)
    {
        return $this->neoxConfigurationRepository->list($request);
    }

    public function store(NeoxConfigurationRequest $request)
    {
        return $this->neoxConfigurationRepository->create($request);
    }

    public function show($id)
    {
        $list = NeoxConfiguration::findOrFail($id);
        return new NeoxConfigurationResource($list);
    }

    public function update(NeoxConfigurationRequest $request, $id)
    {
        return $this->neoxConfigurationRepository->update($request, $id);
    }

    public function destroy($id)
    {
        return $this->neoxConfigurationRepository->delete($id);
    }
}
