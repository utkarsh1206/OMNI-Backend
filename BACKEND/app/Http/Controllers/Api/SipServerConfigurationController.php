<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SipServerConfiguration;
use App\Repositories\SipServerConfigurationRepository;
use Illuminate\Http\Request;
use App\Http\Requests\SipServerConfigurationRequest;

class SipServerConfigurationController extends Controller
{

    protected $sipServerConfigurationRepository;

    public function __construct(SipServerConfigurationRepository $sipServerConfigurationRepository)
    {
        $this->sipServerConfigurationRepository = $sipServerConfigurationRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Delegate listing and filtering to the repository
        return $this->sipServerConfigurationRepository->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SipServerConfigurationRequest $request) // Using the validated Request
    {
        // Pass the validated request data to the repository for creation
        return $this->sipServerConfigurationRepository->create($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(SipServerConfiguration $sipServerConfiguration)
    {
        // Delegate showing a single resource to the repository
        return $this->sipServerConfigurationRepository->show($sipServerConfiguration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        SipServerConfigurationRequest $request,
        SipServerConfiguration $sipServerConfiguration
    ) // Using the validated Request
    {
        // Pass validated request data and the model instance to the repository for update
        return $this->sipServerConfigurationRepository->update($request, $sipServerConfiguration);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SipServerConfiguration $sipServerConfiguration)
    {
        // Delegate deletion to the repository
        return $this->sipServerConfigurationRepository->delete($sipServerConfiguration);
    }
}
