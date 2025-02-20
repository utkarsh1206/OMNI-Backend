<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailConfigure;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


class EmailConfigureController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        // Start the query for tenants
        $query = EmailConfigure::query();

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('type', 'like', '%' . $searchTerm . '%')
                    ->orWhere('smtp_host', 'like', '%' . $searchTerm . '%')
                    ->orWhere('smtp_port', 'like', '%' . $searchTerm . '%')
                    ->orWhere('smtp_user', 'like', '%' . $searchTerm . '%')
                    ->orWhere('smtp_password', 'like', '%' . $searchTerm . '%')
                    ->orWhere('default_type', 'like', '%' . $searchTerm . '%')
                    ->orWhere('default_status', 'like', '%' . $searchTerm . '%')
                    ->orWhere('imap_host', 'like', '%' . $searchTerm . '%')
                    ->orWhere('imap_port', 'like', '%' . $searchTerm . '%')
                    ->orWhere('imap_user', 'like', '%' . $searchTerm . '%')
                    ->orWhere('imap_password', 'like', '%' . $searchTerm . '%')
                    ->orWhere('sender_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('sender_email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('assign_method', 'like', '%' . $searchTerm . '%')
                    ->orWhere('signature', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $email_configure = $query->paginate($perPage);

        // Return the response
        return response()->json([
            'data' => $email_configure->items(),
            'meta' => [
                'current_page' => $email_configure->currentPage(),
                'last_page' => $email_configure->lastPage(),
                'per_page' => $email_configure->perPage(),
                'total' => $email_configure->total(),
            ],
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'imap_host' => 'required',
            'imap_port' => 'required',
            'sender_email' => 'required|unique:email_configures',
            // other validation rules...
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = EmailConfigure::create([
            'type' => $request->type,
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'inbound_id' => $request->inbound_id,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_user' => $request->smtp_user,
            'smtp_password' => $request->smtp_password,
            'default_type' => $request->default_type,
            'default_status' => $request->default_status,
            'imap_host' => $request->imap_host,
            'imap_port' => $request->imap_port,
            'imap_user' => $request->imap_user,
            'imap_password' => $request->imap_password,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'assign_method' => $request->assign_method,
            'signature' => $request->signature,
        ]);
        return response()->json([
            'message' => 'Email Configure Created Successfully',
            'data' => new TenantsResource($users)
        ]);
    }
    public function show(EmailConfigure $email_configure)
    {
        return new TenantsResource($email_configure);
    }
    public function update(Request $request, EmailConfigure $email_configure)
    {
        $email_configure->update([
            'type' => $request->type,
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'inbound_id' => $request->inbound_id,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_user' => $request->smtp_user,
            'smtp_password' => $request->smtp_password,
            'default_type' => $request->default_type,
            'default_status' => $request->default_status,
            'imap_host' => $request->imap_host,
            'imap_port' => $request->imap_port,
            'imap_user' => $request->imap_user,
            'imap_password' => $request->imap_password,
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'assign_method' => $request->assign_method,
            'signature' => $request->signature,
        ]);
        return response()->json([
            'message' => 'Email Configure Updated Successfully',
            'data' => new TenantsResource($email_configure)
        ]);
    }
    public function destroy(EmailConfigure $email_configure)
    {
        $email_configure->delete();
        return response()->json([
            'message' => 'Email Configure Deleted Successfully',
        ]);
    }
}
