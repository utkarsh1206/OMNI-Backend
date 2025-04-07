<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Http\Response\CustomApiResponse;
use App\Models\Users;
use Exception;

class TenantRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $filters = [
                'search' => 'searchTerm',
                'company_name' => 'companyName',
                'email' => 'email',
                'mobile' => 'mobile',
                'domain_name' => 'domainName',
                'company_code' => 'companyCode',
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
                    $query->where($variable, $inputs[$variable]);
                }
            }

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

            if ($request->hasFile('tenant_logo')) {
                $createData['tenant_logo'] = $this->uploadTenantLogo($request);
            }
            $tenants = Tenant::create($createData);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenants, trans('message.tenant_create_msg'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Storing Tenant : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(Request $request, $tenant)
    {
        try {
            $updateData = $this->prepareTenantData($request);

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
            $tenant->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.tenant_delete_msg'));
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
            'footer_url'
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
}
