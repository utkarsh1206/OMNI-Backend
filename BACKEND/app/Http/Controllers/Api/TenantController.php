<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\TenantsResource;
use App\Http\Resources\UsersResource;
use App\Models\Tenant;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\LicenseController;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TenantRepository;
use App\Http\Requests\TenantRequest;

class TenantController extends Controller
{
    protected $licenseController;

    private $tenantRepository;


    public function __construct(LicenseController $licenseController , TenantRepository $tenantRepository)
    {
        $this->licenseController = $licenseController;  // Inject LicenseController
        $this->tenantRepository = $tenantRepository;
    }

    public function index(Request $request)
    {
       return $this->tenantRepository->list($request);
    }

    public function store(TenantRequest $request)
    {
        $licenseResponse = $this->licenseController->decodeLicense($request);
        $licenseData = $licenseResponse->getData(true);

        if (isset($licenseData['number_of_enterprise'])) {
            $currentTenantsCount = Tenant::query()->count();
            $maxTenantsAllowed = $licenseData['number_of_enterprise'];

            if ($currentTenantsCount >= $maxTenantsAllowed) {
                return response()->json([
                    'error' => trans("message.max_tenant_allow") . $maxTenantsAllowed
                ], 400);
            }
        } else {
            return response()->json(['error' => trans("message.license_invalid")], 400);
        }
        return $this->tenantRepository->store($request);
    }
    public function show(Tenant $tenant)
    {
        return new TenantsResource($tenant);
    }
    public function update(Request $request, Tenant $tenant)
    {
        return $this->tenantRepository->update($request,$tenant);
    }
    public function destroy(Tenant $tenant)
    {
       return $this->tenantRepository->delete($tenant);
    }
    public function dashboard()
    {
       return $this->tenantRepository->dashboard();
    }
}
