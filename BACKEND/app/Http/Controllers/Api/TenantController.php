<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\TenantsResource;
use App\Http\Resources\UsersResource;
use App\Models\Tenants;
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

        // $cacheKey = 'tenants_index:' . md5(json_encode($request->all())); // Unique key based on request parameters

        // $tenants = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($request) { // Cache for 60 minutes
        //     $searchTerm = $request->input('search', '');
        //     $companyName = $request->input('company_name', '');
        //     $email = $request->input('email', '');
        //     $mobile = $request->input('mobile', '');
        //     $domainName = $request->input('domain_name', '');
        //     $companyCode = $request->input('company_code', '');
    
        //     $query = Tenants::query()->where('is_delete', 'No');
    
        //     // If there's a search term, apply it to the query
        // if (!empty($searchTerm)) {
        //     $query->where(function ($q) use ($searchTerm) {
        //         $q->where('username', 'like', '%' . $searchTerm . '%')
        //             ->orwhere('first_name', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('email', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('domain_name', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('company_code', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('company_name', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('server_serialnumber', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('server_ip', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('server_mac', 'like', '%' . $searchTerm . '%')
        //             ->orWhere('domain_url', 'like', '%' . $searchTerm . '%');
        //         // ->orWhere('status', 'like', '%' . $searchTerm . '%'); // Add any additional fields as needed
        //     });
        // }

        // if (!empty($companyName)) {
        //     $query->where('company_name', $companyName);
        // }
        // if (!empty($email)) {
        //     $query->where('email', $email);
        // }
        // if (!empty($mobile)) {
        //     $query->where('mobile', $mobile);
        // }
        // if (!empty($domainName)) {
        //     $query->where('domain_name', $domainName);
        // }
        // if (!empty($companyCode)) {
        //     $query->where('company_code', $companyCode);
        // }
    
        //    // Paginate the results
        // $perPage = $request->input('per_page', 10);
        // return $query->paginate($perPage);
        // // $tenants = $query->paginate($perPage);
        // });
    
        // return response()->json([
        //     'data' => $tenants->items(),
        //     'meta' => [
        //         'current_page' => $tenants->currentPage(),
        //         'last_page' => $tenants->lastPage(),
        //         'per_page' => $tenants->perPage(),
        //         'total' => $tenants->total(),
        //     ],
        // ]);


        // Get the search term from the request
       return $this->tenantRepository->list($request);
    }

    public function store(TenantRequest $request)
    {
        $licenseResponse = $this->licenseController->decodeLicense($request);
        $licenseData = $licenseResponse->getData(true);

        if (isset($licenseData['number_of_enterprise'])) {
            $currentTenantsCount = Tenants::query()->count();
            $maxTenantsAllowed = $licenseData['number_of_enterprise'];

            if ($currentTenantsCount >= $maxTenantsAllowed) {
                return response()->json([
                    'error' => 'Tenant creation limit exceeded. Max number of tenants allowed is ' . $maxTenantsAllowed
                ], 400);
            }
        } else {
            return response()->json(['error' => 'License data invalid'], 400);
        }
        $validator = $request->validated();
        return $this->tenantRepository->store($request);
    }
    public function show(Tenants $tenant)
    {
        return new TenantsResource($tenant);
    }
    public function update(Request $request, Tenants $tenant)
    {
        return $this->tenantRepository->update($request,$tenant);
    }
    public function destroy(Tenants $tenant)
    {
       return $this->tenantRepository->delete($tenant);
    }
    public function dashboard()
    {
       return $this->tenantRepository->dashboard();
    }
}
