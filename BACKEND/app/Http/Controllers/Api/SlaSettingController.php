<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SlaSettingRepository;
use App\Http\Requests\SlaSettingRequest;
use App\Models\SlaSetting;

class SlaSettingController extends Controller
{
    private $slaSettingRepository;

    public function __construct(SlaSettingRepository $slaSettingRepository)
    {
        $this->slaSettingRepository = $slaSettingRepository;
    }

    public function index(Request $request)
    {
        return $this->slaSettingRepository->list($request);
    }

    public function store(SlaSettingRequest $request)
    {
        $validatedData = $request->validated();
        return $this->slaSettingRepository->store($validatedData);
    }

    public function show(SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->show($slaSetting);
    }

    public function update(SlaSettingRequest $request, SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->update($request, $slaSetting);
    }

    public function destroy(SlaSetting $slaSetting)
    {
        return $this->slaSettingRepository->delete($slaSetting);
    }
}
