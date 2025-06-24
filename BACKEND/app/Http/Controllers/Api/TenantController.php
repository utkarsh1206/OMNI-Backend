<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\LicenseController;
use App\Repositories\TenantRepository;
use App\Http\Requests\TenantRequest;
use App\Http\Requests\TenantChangeStatusRequest;
use Svg\Tag\Rect;

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
     * @OA\Get(
     *     path="api/tenants",
     *     summary="List tenants with filters and pagination",
     *     description="Retrieve a list of tenants based on optional filters and pagination.",
     *     operationId="listTenants",
     *     tags={"Tenants"},
     *     @OA\Parameter(
     *         name="searchTerm",
     *         in="query",
     *         description="Search term to filter tenants by various fields (e.g., username, email, company name, etc.)",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="companyName",
     *         in="query",
     *         description="Filter tenants by company name",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter tenants by email",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="Filter tenants by mobile number",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="domainName",
     *         in="query",
     *         description="Filter tenants by domain name",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="companyCode",
     *         in="query",
     *         description="Filter tenants by company code",
     *         required=false,
     *         schema={
     *             "type": "string"
     *         }
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         schema={
     *             "type": "integer",
     *             "default": 10
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     ref="#/components/schemas/Tenant"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     *
     * @OA\Schema(
     *     schema="Tenant",
     *     type="object",
     *     @OA\Property(property="id", type="integer"),
     *     @OA\Property(property="username", type="string"),
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="company_name", type="string"),
     *     @OA\Property(property="domain_name", type="string"),
     *     @OA\Property(property="company_code", type="string"),
     *     @OA\Property(property="mobile", type="string")
     * )
     */
    public function index(Request $request)
    {
        return $this->tenantRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/tenants",
     *     summary="Create a new tenant",
     *     description="Create a new tenant with the provided details, including uploading an optional tenant logo image.",
     *     operationId="createTenant",
     *     tags={"Tenants"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={
     *                     "username", "first_name", "last_name", "email", "password",
     *                     "mobile", "domain_name", "company_code", "company_name",
     *                     "server_serialnumber", "server_ip", "server_mac", "domain_url", "status", "tenant_role_id"
     *                 },
     *                 @OA\Property(property="username", type="string", description="Username of the tenant"),
     *                 @OA\Property(property="first_name", type="string", description="First name of the tenant"),
     *                 @OA\Property(property="last_name", type="string", description="Last name of the tenant"),
     *                 @OA\Property(property="email", type="string", format="email", description="Email of the tenant"),
     *                 @OA\Property(property="password", type="string", format="password", description="Password for the tenant account"),
     *                 @OA\Property(property="mobile", type="string", description="Mobile number of the tenant"),
     *                 @OA\Property(property="domain_name", type="string", description="Domain name of the tenant"),
     *                 @OA\Property(property="company_code", type="string", description="Company code of the tenant"),
     *                 @OA\Property(property="company_name", type="string", description="Company name of the tenant"),
     *                 @OA\Property(property="server_serialnumber", type="string", description="Server serial number"),
     *                 @OA\Property(property="server_ip", type="string", description="Server IP address"),
     *                 @OA\Property(property="server_mac", type="string", description="Server MAC address"),
     *                 @OA\Property(property="domain_url", type="string", description="URL of the domain"),
     *                 @OA\Property(property="status", type="string", description="Status of the tenant"),
     *                 @OA\Property(property="tenant_role_id", type="integer", description="Tenant role ID"),
     *                 @OA\Property(property="title", type="string", description="Title of the tenant (optional)"),
     *                 @OA\Property(property="footer_content", type="string", description="Footer content of the tenant (optional)"),
     *                 @OA\Property(property="footer_url", type="string", description="Footer URL for the tenant (optional)"),
     *                 @OA\Property(property="tenant_logo", type="string", format="binary", description="Tenant logo image file (optional)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tenant"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
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
     * @OA\Get(
     *     path="/tenants/{tenant}",
     *     summary="Get tenant details",
     *     description="Retrieve the details of a specific tenant by ID.",
     *     operationId="getTenantDetails",
     *     tags={"Tenants"},
     *     @OA\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tenant"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
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
     * @OA\Put(
     *     path="/tenants/{tenant}",
     *     summary="Update an existing tenant",
     *     description="Update the details of an existing tenant. Optionally, upload a new tenant logo image.",
     *     operationId="updateTenant",
     *     tags={"Tenants"},
     *     @OA\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"first_name", "last_name", "email"},
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string"),
     *                 @OA\Property(property="password", type="string", format="password"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="mobile", type="string"),
     *                 @OA\Property(property="company_code", type="string"),
     *                 @OA\Property(property="company_name", type="string"),
     *                 @OA\Property(property="server_serialnumber", type="string"),
     *                 @OA\Property(property="server_ip", type="string"),
     *                 @OA\Property(property="server_mac", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="tenant_role_id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="footer_content", type="string"),
     *                 @OA\Property(property="footer_url", type="string"),
     *                 @OA\Property(property="tenant_logo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant successfully updated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Tenant"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
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
     * @OA\Delete(
     *     path="/tenants/{tenant}",
     *     summary="Delete a tenant",
     *     description="Delete a specific tenant by ID.",
     *     operationId="deleteTenant",
     *     tags={"Tenants"},
     *     @OA\Parameter(
     *         name="tenant",
     *         in="path",
     *         description="ID of the tenant to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tenant successfully deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={"id": 1, "message": "Tenant deleted"}
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Tenant not found"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
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
     * @OA\Get(
     *     path="/dashboard",
     *     summary="Retrieve dashboard statistics",
     *     description="Fetches various statistics like ticket types, priorities, agent status, and call counts for the dashboard.",
     *     operationId="getDashboardStats",
     *     tags={"Tenants"},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Success"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="type_wise_ticket",
     *                     type="object",
     *                     additionalProperties={
     *                         @OA\Property(
     *                             property="type",
     *                             type="string",
     *                             example="Problem"
     *                         ),
     *                         @OA\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=1
     *                         )
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="priority_wise_ticket",
     *                     type="object",
     *                     additionalProperties={
     *                         @OA\Property(
     *                             property="priority",
     *                             type="string",
     *                             example="High"
     *                         ),
     *                         @OA\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=5
     *                         )
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="agent_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @OA\Property(
     *                             property="agent_id",
     *                             type="string",
     *                             example="ABC 101"
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="array",
     *                             @OA\Items(
     *                                 @OA\Property(
     *                                     property="status",
     *                                     type="string",
     *                                     example="Login"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="ip_address",
     *                                     type="string",
     *                                     example="192.168.1.110"
     *                                 )
     *                             )
     *                         )
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="lead_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="Open"
     *                         ),
     *                         @OA\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=100
     *                         )
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="call_count_status",
     *                     type="object",
     *                     additionalProperties={
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="Success"
     *                         ),
     *                         @OA\Property(
     *                             property="count",
     *                             type="integer",
     *                             example=200
     *                         )
     *                     }
     *                 ),
     *                 @OA\Property(
     *                     property="agent_count",
     *                     type="integer",
     *                     example=10
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
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

    /**
     * @OA\Get(
     *     path="/api/check-tenant-creation",
     *     summary="Check if tenant creation is allowed",
     *     tags={"Tenants"},
     *     @OA\Response(
     *         response=200,
     *         description="Check result",
     *         @OA\JsonContent(
     *             @OA\Property(property="allowed", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tenant creation is allowed.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="License invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="License is invalid or missing.")
     *         )
     *     )
     * )
     */
    public function checkTenantCreationAllowed(Request $request)
    {
        generateLogs('info', 'request ' . $request);
        $licenseResponse = $this->licenseController->decodeLicense($request);
        $licenseData = $licenseResponse->getData(true);
        generateLogs('info', 'License Data ' . json_encode($licenseData));
        if (!isset($licenseData['number_of_enterprise'])) {
            return response()->json(['error' => trans("message.license_invalid")], 400);
        }

        $currentTenantsCount = Tenant::query()->count();
        $maxTenantsAllowed = $licenseData['number_of_enterprise'];

        if ($currentTenantsCount >= $maxTenantsAllowed) {
            return response()->json([
                'allowed' => false,
                'message' => trans("message.max_tenant_allow") . $maxTenantsAllowed
            ], 200);
        }

        return response()->json([
            'allowed' => true,
            'message' => trans("message.tenant_creation_allowed")
        ], 200);
    }


    public function changeStatus(TenantChangeStatusRequest $request)
    {
       return $this->tenantRepository->changeStatus($request);
    }
}
