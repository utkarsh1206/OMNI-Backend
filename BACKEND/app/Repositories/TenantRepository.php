<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Http\Response\CustomApiResponse;
use App\Models\Users;
use App\Services\HostedTenantService;   // NOC-995 To-Do Hosted MultiTenant
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class TenantRepository
{
    private $apiResponse;
    protected $hostedTenantService; // NOC-995 To-Do Hosted MultiTenant

    public function __construct(
        CustomApiResponse $customApiResponse,
        HostedTenantService $hostedTenantService    // NOC-995 To-Do Hosted MultiTenant
    ) {
        $this->apiResponse = $customApiResponse;
        $this->hostedTenantService = $hostedTenantService;  // NOC-995 To-Do Hosted MultiTenant
    }

    public function list(Request $request)
    {
        try {
            $filters = [
                'search' => 'searchTerm',
                'company_name' => 'company_name',
                'email' => 'email',
                'mobile' => 'mobile',
                'domain_name' => 'domain_name',
                'company_code' => 'company_code',
            ];

            // Retrieve input values
            $inputs = [];
            foreach ($filters as $key => $variable) {
                $inputs[$variable] = $request->input($key, '');
            }

            // Start the query for tenants
            $query = Tenant::query();

            // Apply search term conditions if available
            if (!empty($inputs['searchTerm'])) {
                $query->where(function ($q) use ($inputs) {
                    $searchTerm = $inputs['searchTerm'];
                    $q->where('username', 'like', '%' . $searchTerm . '%')
                        ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
                        ->orWhere('domain_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('company_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('company_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('server_serialnumber', 'like', '%' . $searchTerm . '%')
                        ->orWhere('server_ip', 'like', '%' . $searchTerm . '%')
                        ->orWhere('server_mac', 'like', '%' . $searchTerm . '%')
                        ->orWhere('domain_url', 'like', '%' . $searchTerm . '%');
                });
            }

            // Apply other filters dynamically
            foreach ($filters as $key => $variable) {
                if (!empty($inputs[$variable]) && $variable !== 'searchTerm') {
                    $query->where($variable, 'like', '%' . $inputs[$variable] . '%');
                }
            }

            $query = $query->orderBy('id', 'desc');
            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $tenants = $query->paginate($perPage);

            // Return the response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenants, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Tenant List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $createData = $this->prepareTenantData($request);
            // NOC-995 To-Do Hosted MultiTenant START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $externalPayload = [
                    "owner_name"      => $request->first_name . ' ' . $request->last_name,
                    "domain"          => $request->domain_name,
                    "email_id"        => $request->email,
                    "phone"           => $request->mobile,
                    "enterprise_name" => $request->company_name,
                    "contact_address" => $request->contact_address ?? 'N/A',
                ];

                $this->hostedTenantService->createExternalTenant($externalPayload);
            }
            // NOC-995 To-Do Hosted MultiTenant END

            if ($request->hasFile('tenant_logo')) {
                $createData['tenant_logo'] = $this->uploadTenantLogo($request);
            }
            $tenants = Tenant::create($createData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $tenants,
                trans('message.tenant_create_msg')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(Request $request, $tenant)
    {
        try {
            $updateData = $this->prepareTenantData($request);
            // NOC-995 To-Do Hosted MultiTenant START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $externalPayload = [
                    "owner_name"      => ($request->first_name ?? $tenant->first_name) . ' ' . ($request->last_name ?? $tenant->last_name),
                    "email_id"        => $request->email ?? $tenant->email,
                    "phone"           => $request->mobile ?? $tenant->mobile,
                    "status"          => strtolower($request->status ?? $tenant->status),
                    "alternate_phone" => $request->alternate_phone ?? '',
                    "contact_address" => $request->contact_address ?? 'N/A',
                ];

                $this->hostedTenantService->updateExternalTenant($tenant->domain_name, $externalPayload);
            }
            // NOC-995 To-Do Hosted MultiTenant END

            if ($request->hasFile('tenant_logo')) {
                $updateData['tenant_logo'] = $this->uploadTenantLogo($request);
            }

            // Update the tenant record
            $tenant->update($updateData);

            // Return response
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.tenant_update_msg'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Updating Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($tenant)
    {
        try {
            // NOC-995 To-Do Hosted MultiTenant START
            if (config('omni.IS_DC_MULTITENANT') == 1) {
                $domain = $tenant->domain_name;

                if (empty($domain)) {
                    throw new Exception("Domain name is missing for this tenant. Cannot delete from external system.");
                }

                $this->hostedTenantService->deleteExternalTenant($domain);
            }
            // NOC-995 To-Do Hosted MultiTenant END

            $tenant->delete();
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $tenant,
                trans('message.tenant_delete_msg')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Deleting Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($tenant)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Showing Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function dashboard()
    {
        $type_wise_ticket = array("Problem" => "1", "Help" => "0", "Incident" => "2", "Questions" => "5", "Feature Request" => "0", "Other" => 10);
        $priority_wise_ticket = array("High" => "5", "Medium" => "2", "Low" => "0", "Urgent" => "10");
        $agent_status = array("ABC 101" => array("Login", "Pause", "192.168.1.110"), "BCD 201" => array("Login", "Resume", "192.168.1.111"), "XYZ 301" => array("Logout", "Pause", "192.168.1.112"));
        $lead_status = array("Unassigned" => "0", "Open" => "100", "Waiting On Customer" => "5", "Waiting On Third party" => "5", "Resolved" => "50", "Closed" => "200");
        $call_count_status = array("Success" => "200", "Failed" => "30");
        $agent_count =  Users::query()->where('type', '3')->count();
        $final_array = array("type_wise_ticket" => $type_wise_ticket, "priority_wise_ticket" => $priority_wise_ticket, "agent_status" => $agent_status, "lead_status" => $lead_status, "call_count_status" => $call_count_status, "agent_count" => $agent_count);

        return $this->apiResponse->getResponseStructure(trans('message.success'), $final_array, trans('message.data_fetch'));
    }

    protected function prepareTenantData(Request $request)
    {
        $data = $request->only([
            'first_name',
            'username',
            'last_name',
            'email',
            'mobile',
            'company_code',
            'company_name',
            'server_serialnumber',
            'server_ip',
            'server_mac',
            'status',
            'tenant_role_id',
            'title',
            'footer_content',
            'footer_url',
            'domain_name',
            'domain_url',
        ]);

        // Hash the password only if it's present in the request
        if ($request->has('password')) {
            $data['password'] = bcrypt($request->password);
        }

        return $data;
    }

    protected function uploadTenantLogo(Request $request)
    {
        $tenant_logo = $request->file('tenant_logo');
        $filename = now()->format('Y_m_d_H_i_s') . '.' . $tenant_logo->getClientOriginalExtension();

        return $tenant_logo->storeAs('tenant_logo', $filename, 'public');
    }

    public function changeStatus(Request $request)
    {
        try {
            $tenantInfo = Tenant::find($request->tenant_id);

            if (!$tenantInfo) {
                return response()->json([
                    'message' => trans("message.tenant_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            // NOC-995 To-Do Hosted MultiTenant Start
            if (config('omni.IS_DC_MULTITENANT') == 1) {

                $externalPayload = [
                    "owner_name"      => $tenantInfo->first_name . ' ' . $tenantInfo->last_name,
                    "email_id"        => $tenantInfo->email,
                    "phone"           => $tenantInfo->mobile,
                    "status"          => strtolower($request->status),
                    "alternate_phone" => $tenantInfo->alternate_phone ?? '',
                    "contact_address" => $tenantInfo->contact_address ?? 'N/A',
                ];

                $this->hostedTenantService->updateExternalTenant($tenantInfo->domain_name, $externalPayload);
            }
            // NOC-995 To-Do Hosted MultiTenant END

            $tenantInfo->status = $request->status;
            $tenantInfo->save();

            getDbConnection($tenantInfo->domain_name, true);

            Users::query()->update(['status' => $request->status]);

            establishMasterConnection(auth()->user()->domain, "omnichannel", "mysql");

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $tenantInfo,
                trans('message.tenant_update_msg')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in change status Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
