<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TenantsResource;
use App\Http\Response\CustomApiResponse;
use App\Models\Users;


class TenantRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
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
        $perPage = $request->input('per_page', 10);
        $tenants = $query->paginate($perPage);
        
        // Return the response
        return $this->apiResponse->getResponseStructure(trans('message.success'), $tenants, trans('message.data_fetch'));
    }

    public function store(Request $request)
    {
        $tenant_logo_url = "";
        if ($request->hasFile('tenant_logo')) {
            $tenant_logo = $request->file('tenant_logo');
            $filename = now()->format('Y_m_d_H_i_s') . '.' . $tenant_logo->getClientOriginalExtension();
            // Store the image in the 'public' disk (storage/app/public)
            $path = $tenant_logo->storeAs('tenant_logo', $filename, 'public');

            // Return the URL to access the uploaded image
            $tenant_logo_url = url('storage/' . $path);;
        }

        $tenants = Tenant::create([
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Ensure to hash the password
            'mobile' => $request->mobile,
            'domain_name' => $request->domain_name,
            'company_code' => $request->company_code,
            'company_name' => $request->company_name,
            'server_serialnumber' => $request->server_serialnumber,
            'server_ip' => $request->server_ip,
            'server_mac' => $request->server_mac,
            'domain_url' => $request->domain_url,
            'status' => $request->status,
            'tenant_role_id' => $request->tenant_role_id,
            'title' => $request->title,
            'footer_content' => $request->footer_content,
            'footer_url' => $request->footer_url,
            'tenant_logo' => $tenant_logo_url
        ]);
        
        return $this->apiResponse->getResponseStructure(trans('message.success'), $tenants, trans('message.tenant_create_msg'));
    }


    public function update(Request $request, $tenant)
    {
        $tenant_logo_url = "";
        if ($request->hasFile('tenant_logo')) {
            $tenant_logo = $request->file('tenant_logo');
            $filename = now()->format('Y_m_d_H_i_s') . '.' . $tenant_logo->getClientOriginalExtension();
            // Store the image in the 'public' disk (storage/app/public)
            $path = $tenant_logo->storeAs('tenant_logo', $filename, 'public');

            // Return the URL to access the uploaded image
            $tenant_logo_url = url('storage/' . $path);;
        }
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => bcrypt($request->password),
            'email' => $request->email,
            'mobile' => $request->mobile,
            'company_code' => $request->company_code,
            'company_name' => $request->company_name,
            'server_serialnumber' => $request->server_serialnumber,
            'server_ip' => $request->server_ip,
            'server_mac' => $request->server_mac,
            'status' => $request->status,
            'tenant_role_id' => $request->tenant_role_id,
            'title' => $request->title,
            'footer_content' => $request->footer_content,
            'footer_url' => $request->footer_url,
        ];

        // Add tenant_logo to the array only if it is not empty or null
        if (!empty($tenant_logo_url)) {
            $updateData['tenant_logo'] = $tenant_logo_url;
        }

        // Now update the tenant with the filtered data
        $tenant->update($updateData);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.tenant_update_msg'));
    }

    public function delete($tenant)
    {
        $tenant->delete();
        return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.tenant_delete_msg'));
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
}
