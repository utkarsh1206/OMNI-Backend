<?php

namespace App\Repositories;

use App\Models\LiveAgents;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

class LiveAgentRepository
{
    public function list(Request $request)
    {
        $query = LiveAgents::query()->with(['user', 'ingroup']);

        $searchTerm = $request->input('search', '');
        $campaign = $request->input('campaign', '');
        $ingroup = $request->input('ingroup', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('campaign_id', 'like', "%{$searchTerm}%")
                    ->orWhere('user_id', 'like', "%{$searchTerm}%");
            });
        }

        if (!empty($campaign)) {
            $query->where('campaign_id', $campaign);
        }

        if (!empty($ingroup)) {
            $query->where('ingroup_id', $ingroup);
        }

        return $page === 'all'
            ? ['data' => $query->get()]
            : [
                'data' => $query->paginate($perPage)->items(),
                'meta' => [
                    'current_page' => $query->paginate($perPage)->currentPage(),
                    'last_page' => $query->paginate($perPage)->lastPage(),
                    'per_page' => $query->paginate($perPage)->perPage(),
                    'total' => $query->paginate($perPage)->total(),
                ]
            ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
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

        return LiveAgents::create($data);
    }

    public function show($id)
    {
        return LiveAgents::with(['campaign', 'ingroup'])->findOrFail($id);
    }

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

        return $liveAgent;
    }

    public function delete($id)
    {
        $liveAgent = LiveAgents::findOrFail($id);
        $liveAgent->delete();

        return ['message' => 'Live agent record deleted successfully'];
    }
}
