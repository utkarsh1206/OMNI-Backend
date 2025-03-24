<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\LicenseController;
use App\Repositories\TenantRepository;
use App\Http\Requests\TenantRequest;
use Swagger\Annotations as SWG;

class TenantController extends Controller
{
    protected $licenseController;

    private $tenantRepository;

    public function __construct(LicenseController $licenseController, TenantRepository $tenantRepository)
    {
        $this->licenseController = $licenseController;  // Inject LicenseController
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * @SWG\Get(
     *     path="/tenants",
     *     summary="List tenants with filters and pagination",
     *     description="Retrieve a list of tenants based on optional filters and pagination.",
     *     operationId="listTenants",
     *     tags={"Tenants"},
     *     @SWG\Parameter(
     *         name="searchTerm",
     *         in="query",
     *         description="Search term to filter tenants by various fields (e.g., username, email, company name, etc.)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="companyName",
     *         in="query",
     *         description="Filter tenants by company name",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter tenants by email",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="Filter tenants by mobile number",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="domainName",
     *         in="query",
     *         description="Filter tenants by domain name",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="companyCode",
     *         in="query",
     *         description="Filter tenants by company code",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         type="integer",
     *         default="10"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful operation",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 type="array",
     *                 @SWG\Items(
     *                     ref="#/definitions/Tenant"
     *                 )
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Bad Request",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->tenantRepository->list($request);
    }

    /**
     * @SWG\Post(
     *     path="/tenants",
     *     summary="Create a new tenant",
     *     description="Create a new tenant with the provided details, including uploading an optional tenant logo image.",
     *     operationId="createTenant",
     *     tags={"Tenants"},
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         description="Username of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="first_name",
     *         in="formData",
     *         description="First name of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="last_name",
     *         in="formData",
     *         description="Last name of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="Email of the tenant (required)",
     *         required=true,
     *         type="string",
     *         format="email"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="Password for the tenant account (required)",
     *         required=true,
     *         type="string",
     *         format="password"
     *     ),
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="Mobile number of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="domain_name",
     *         in="formData",
     *         description="Domain name of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="company_code",
     *         in="formData",
     *         description="Company code of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="company_name",
     *         in="formData",
     *         description="Company name of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_serialnumber",
     *         in="formData",
     *         description="Server serial number (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_ip",
     *         in="formData",
     *         description="Server IP address (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_mac",
     *         in="formData",
     *         description="Server MAC address (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="domain_url",
     *         in="formData",
     *         description="URL of the domain (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="Status of the tenant (required)",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="tenant_role_id",
     *         in="formData",
     *         description="Tenant role ID (required)",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="title",
     *         in="formData",
     *         description="Title of the tenant (optional)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="footer_content",
     *         in="formData",
     *         description="Footer content of the tenant (optional)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="footer_url",
     *         in="formData",
     *         description="Footer URL for the tenant (optional)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="tenant_logo",
     *         in="formData",
     *         description="Tenant logo image file (optional)",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Tenant successfully created",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 ref="#/definitions/Tenant"
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
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

    /**
     * @SWG\Get(
     *     path="/tenants/{tenant}",
     *     summary="Get tenant details",
     *     description="Retrieve the details of a specific tenant by ID.",
     *     operationId="getTenantDetails",
     *     tags={"Tenants"},
     *     @SWG\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to retrieve",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Tenant details retrieved successfully",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 ref="#/definitions/Tenant"
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
    public function show(Tenant $tenant)
    {
        return $this->tenantRepository->show($tenant);
    }

    /**
     * @SWG\Put(
     *     path="/tenants/{tenant}",
     *     summary="Update an existing tenant",
     *     description="Update the details of an existing tenant. Optionally, upload a new tenant logo image.",
     *     operationId="updateTenant",
     *     tags={"Tenants"},
     *     @SWG\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to update",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="first_name",
     *         in="formData",
     *         description="First name of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="last_name",
     *         in="formData",
     *         description="Last name of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         description="Password for the tenant account (required to hash the password)",
     *         required=false,
     *         type="string",
     *         format="password"
     *     ),
     *     @SWG\Parameter(
     *         name="email",
     *         in="formData",
     *         description="Email of the tenant",
     *         required=false,
     *         type="string",
     *         format="email"
     *     ),
     *     @SWG\Parameter(
     *         name="mobile",
     *         in="formData",
     *         description="Mobile number of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="company_code",
     *         in="formData",
     *         description="Company code of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="company_name",
     *         in="formData",
     *         description="Company name of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_serialnumber",
     *         in="formData",
     *         description="Server serial number",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_ip",
     *         in="formData",
     *         description="Server IP address",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="server_mac",
     *         in="formData",
     *         description="Server MAC address",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="formData",
     *         description="Status of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="tenant_role_id",
     *         in="formData",
     *         description="Tenant role ID",
     *         required=false,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         name="title",
     *         in="formData",
     *         description="Title of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="footer_content",
     *         in="formData",
     *         description="Footer content of the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="footer_url",
     *         in="formData",
     *         description="Footer URL for the tenant",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="tenant_logo",
     *         in="formData",
     *         description="Tenant logo image file (optional)",
     *         required=false,
     *         type="file"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Tenant successfully updated",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 ref="#/definitions/Tenant"
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
    public function update(Request $request, Tenant $tenant)
    {
        return $this->tenantRepository->update($request, $tenant);
    }

    /**
     * @SWG\Delete(
     *     path="/tenants/{tenant}",
     *     summary="Delete a tenant",
     *     description="Delete a specific tenant by ID.",
     *     operationId="deleteTenant",
     *     tags={"Tenants"},
     *     @SWG\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to delete",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Tenant successfully deleted",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"id": 1, "message": "Tenant deleted"}
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */

    public function destroy(Tenant $tenant)
    {
        return $this->tenantRepository->delete($tenant);
    }

    
    /**
     * @SWG\Get(
     *     path="/dashboard",
     *     summary="Retrieve dashboard statistics",
     *     description="Fetches various statistics like ticket types, priorities, agent status, and call counts for the dashboard.",
     *     operationId="getDashboardStats",
     *     tags={"Dashboard"},
     *     @SWG\Response(
     *         response=200,
     *         description="Dashboard statistics fetched successfully",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @SWG\Property(
     *                 property="data",
     *                 type="object",
     *                 @SWG\Property(
     *                     property="type_wise_ticket",
     *                     type="object",
     *                     additionalProperties={
     *                         @SWG\Property(
     *                             property="type",
     *                             type="string",
     *                             example="Problem"
     *                         ),
     *                         @SWG\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=1
     *                         )
     *                     }
     *                 ),
     *                 @SWG\Property(
     *                     property="priority_wise_ticket",
     *                     type="object",
     *                     additionalProperties={
     *                         @SWG\Property(
     *                             property="priority",
     *                             type="string",
     *                             example="High"
     *                         ),
     *                         @SWG\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=5
     *                         )
     *                     }
     *                 ),
     *                 @SWG\Property(
     *                     property="agent_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @SWG\Property(
     *                             property="agent_id",
     *                             type="string",
     *                             example="ABC 101"
     *                         ),
     *                         @SWG\Property(
     *                             property="status",
     *                             type="array",
     *                             items={
     *                                 @SWG\Property(
     *                                     property="status",
     *                                     type="string",
     *                                     example="Login"
     *                                 ),
     *                                 @SWG\Property(
     *                                     property="ip_address",
     *                                     type="string",
     *                                     example="192.168.1.110"
     *                                 )
     *                             }
     *                         )
     *                     }
     *                 ),
     *                 @SWG\Property(
     *                     property="lead_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @SWG\Property(
     *                             property="status",
     *                             type="string",
     *                             example="Open"
     *                         ),
     *                         @SWG\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=100
     *                         )
     *                     }
     *                 ),
     *                 @SWG\Property(
     *                     property="call_count_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @SWG\Property(
     *                             property="status",
     *                             type="string",
     *                             example="Success"
     *                         ),
     *                         @SWG\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=200
     *                         )
     *                     }
     *                 ),
     *                 @SWG\Property(
     *                     property="agent_count",
     *                     type="integer",
     *                     example=10
     *                 )
     *             ),
     *             @SWG\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @SWG\Schema(
     *             type="object",
     *             @SWG\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
    public function dashboard()
    {
        return $this->tenantRepository->dashboard();
    }
}
