<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use Exception;
use Illuminate\Support\Facades\Log;

class SupportTicketRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $token = $request->bearerToken();
        if ($token != "") {
            $payload = JWTAuth::setToken($token)->getPayload();
            $roleId = $payload['role_id'];
            $userId = $payload['id'];
        }

        // Start the query for tenants
        $query = supportticket::query();

        if ($roleId == 3) {
            $query->where('assign_to_id', $userId); // Filter by the logged-in user's ID
        }

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('to_emails', 'like', '%' . $searchTerm . '%')
                    ->orwhere('subject', 'like', '%' . $searchTerm . '%')
                    ->orwhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $supportticket = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $supportticket = $query->get();
            return response()->json([
                'data' => $supportticket,
            ]);
        } else {
            // Paginate the results
            $supportticket = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $supportticket->items(),
                'meta' => [
                    'current_page' => $supportticket->currentPage(),
                    'last_page' => $supportticket->lastPage(),
                    'per_page' => $supportticket->perPage(),
                    'total' => $supportticket->total(),
                ],
            ]);
        }
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
            $tenant_logo_url = url('storage/' . $path);
            ;
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
}
