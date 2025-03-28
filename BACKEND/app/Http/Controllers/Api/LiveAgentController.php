<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveAgents;
use Illuminate\Http\Request;

class LiveAgentController extends Controller
{
    /**
     * Display a listing of the live agents.
     */
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        $campaign = $request->input('campaign', '');
        $ingroup = $request->input('ingroup', '');
        // Start the query for tenants
        $query = LiveAgents::query()->with(['user','campaign', 'ingroup']);

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('campaign_id', 'like', '%' . $searchTerm . '%')
                    ->orwhere('user_id', 'like', '%' . $searchTerm . '%');
            });
        }

        if (!empty($campaign)) {
            $query->where('campaign_id', $campaign);
        }
        if (!empty($ingroup)) {
            $query->where('ingroup_id', $ingroup);
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $live_agent = $query->paginate($perPage);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $live_agent = $query->get();
            return response()->json([
                'data' => $live_agent,
            ]);
        } else {
            // Paginate the results
            $live_agent = $query->paginate($perPage);
            // Return the response with pagination meta
            return response()->json([
                'data' => $live_agent->items(),
                'meta' => [
                    'current_page' => $live_agent->currentPage(),
                    'last_page' => $live_agent->lastPage(),
                    'per_page' => $live_agent->perPage(),
                    'total' => $live_agent->total(),
                ],
            ]);
        }
    }

    /**
     * Store a newly created live agent entry.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'extension' => 'required|string',
            'status' => 'required|string',
            'customer' => 'nullable|string',
            'campaign_id' => 'required|exists:ecdial_campaigns,id',
            'ingroup_id' => 'required|exists:ecdial_inbound_groups,id',
            'status_last_updated_at' => 'nullable|date',
            'total_calls' => 'nullable|integer',
            'login_time' => 'nullable|date',
            'ready_hr' => 'nullable|date',
            'talk_hr' => 'nullable|date',
            'talk_avg' => 'nullable|numeric',
            'ingroup' => 'nullable|string',
            'lagged_duration' => 'nullable|integer',
            'rank' => 'nullable|integer',
        ]);

        $liveAgent = LiveAgents::create($validated);
        return response()->json($liveAgent, 201);
    }

    /**
     * Display the specified live agent.
     */
    public function show($id)
    {
        $liveAgent = LiveAgents::with(['campaign', 'ingroup'])->findOrFail($id);
        return response()->json($liveAgent);
    }

    /**
     * Update the specified live agent entry.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'nullable|string',
            'customer' => 'nullable|string',
            'status_last_updated_at' => 'nullable|date',
            'total_calls' => 'nullable|integer',
            'talk_hr' => 'nullable|date',
            'talk_avg' => 'nullable|numeric',
            'lagged_duration' => 'nullable|integer',
            'rank' => 'nullable|integer',
        ]);

        $liveAgent = LiveAgents::findOrFail($id);
        $liveAgent->update($validated);

        return response()->json($liveAgent);
    }

    /**
     * Remove the specified live agent entry.
     */
    public function destroy($id)
    {
        $liveAgent = LiveAgents::findOrFail($id);
        $liveAgent->delete();

        return response()->json(['message' => 'Live agent record deleted successfully']);
    }
}
