<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Tenants;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\DB;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Artisan;
use App\Models\Users;
use Illuminate\Support\Facades\Config;

class TenantRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $companyName = $request->input('company_name', '');
        $email = $request->input('email', '');
        $mobile = $request->input('mobile', '');
        $domainName = $request->input('domain_name', '');
        $companyCode = $request->input('company_code', '');

        // Start the query for tenants
        $query = Tenants::query();

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('username', 'like', '%' . $searchTerm . '%')
                    ->orwhere('first_name', 'like', '%' . $searchTerm . '%')
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
                // ->orWhere('status', 'like', '%' . $searchTerm . '%'); // Add any additional fields as needed
            });
        }

        if (!empty($companyName)) {
            $query->where('company_name', $companyName);
        }
        if (!empty($email)) {
            $query->where('email', $email);
        }
        if (!empty($mobile)) {
            $query->where('mobile', $mobile);
        }
        if (!empty($domainName)) {
            $query->where('domain_name', $domainName);
        }
        if (!empty($companyCode)) {
            $query->where('company_code', $companyCode);
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

        $tenants = Tenants::create([
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
        $lastInsertedId = $tenants->id;

        $databaseName = str_replace(".", "_", $request->domain_name);

        DB::statement("CREATE DATABASE $databaseName");
        $details_array = [
            'db' => str_replace(".", "_", $request->domain_name),
            'domain' => $request->domain_name,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
        ];
        DB::table('details')->insert($details_array);
        $newConnectionName = str_replace(".", "_", $request->domain_name);

        Config::set("database.connections.$newConnectionName", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $databaseName,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
        Artisan::call('migrate', [
            '--database' => $newConnectionName,
            '--path'     => '/database/migrations/user', // Adjust the path as needed
            '--force'    => true,
        ]);
        Users::on($newConnectionName)->create([
            'tenant_id'  => $lastInsertedId,
            'type' => 0,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'password'   => bcrypt($request->password),
            'email'      => $request->email,
            'username'   => $request->username,
            'domain'     => $request->domain_url,
            'mobile'     => $request->mobile,
            'status' => 'Active',
            'role_id' => 1
        ]);

        Artisan::call('db:seed', [
            '--database' => $newConnectionName,
            '--class' => 'TenantSeeder',
            '--force' => true, // Run seeders in production if necessary
        ]);
        return response()->json([
            'message' => trans("message.tenant_create_msg"),
            'data' => new TenantsResource($tenants)
        ]);
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

        // Cache::forget('tenants_index:' . md5(json_encode($request->all()))); // Clear the tenants index cache (adapt the key if needed)

        return response()->json([
            'message' =>  trans("message.tenant_update_msg"),
            'data' => new TenantsResource($tenant)
        ]);
    }

    public function delete($tenant)
    {
        $tenant->delete();
        return response()->json([
            'message' => trans("message.tenant_delete_msg"),
        ]);
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
        return response()->json([
            'data' => new TenantsResource($final_array)
        ]);
    }
}
