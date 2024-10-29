<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supportticketgroup;
use App\Http\Resources\TenantsResource;
use App\Models\Supportticket;
use Illuminate\Support\Facades\Validator;

class SupportticketController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');

        // Start the query for tenants
        $query = supportticket::query()->where('is_delete', 'No');

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
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|unique:supportticket',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $supportticket = SupportTicket::create([
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'inbound_group_id' => $request->inbound_group_id,
            'contact_id' => $request->contact_id,
            'assign_to_id' => $request->assign_to_id,
            'default_type' => $request->default_type,
            'default_priority' => $request->default_priority,
            'default_status' => $request->default_status,
            'subject' => $request->subject,
            'departments' => $request->departments,
            'to_emails' => $request->to_emails,
            'description' => $request->description,
            'attachment' => $request->attachment,
            'status' => $request->status,
        ]);
        return response()->json([
            'message' => 'Support Ticket Created Successfully',
            'data' => new TenantsResource($supportticket)
        ]);
    }
    public function show(Supportticket $supportticket)
    {
        return new TenantsResource($supportticket);
    }
    public function update(Request $request, Supportticket $supportticket)
    {
        $supportticket->update([
            'product_id' => $request->product_id,
            'campaign_id' => $request->campaign_id,
            'inbound_group_id' => $request->inbound_group_id,
            'contact_id' => $request->contact_id,
            'assign_to_id' => $request->assign_to_id,
            'default_type' => $request->default_type,
            'default_priority' => $request->default_priority,
            'default_status' => $request->default_status,
            'subject' => $request->subject,
            'departments' => $request->departments,
            'to_emails' => $request->to_emails,
            'description' => $request->description,
            'attachment' => $request->attachment,
            'status' => $request->status,
        ]);
        return response()->json([
            'message' => 'Support Ticket Updated Successfully',
            'data' => new TenantsResource($supportticket)
        ]);
    }    
    public function destroy(Supportticket $supportticket)
    {
        $supportticket->is_delete = 'Yes';
        $supportticket->save();
        return response()->json([
            'message' => 'Support Ticket Deleted Successfully',
        ]);
    }
}
