<?php

namespace App\Repositories;

use App\Models\LiveAgentDailyMetric;
use App\Models\LiveAgents;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LiveAgentRepository
{
    public function list(Request $request)
    {
        $this->refreshLoginTimesForToday();

        $query = LiveAgents::query()->with(['user', 'ingroup']);

        $searchTerm = $request->input('search', '');
        $campaign = $request->input('campaign', '');
        $ingroup = $request->input('ingroup', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $status = $request->input('status', '');

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

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $token = $request->bearerToken(); // Get token from header
        $payload = extractPayload($token);

        $roleId = $payload['role_id'] ?? null;
        $campaignFromToken = $payload['campaign'] ?? null;

        if ($roleId == 3) {

            if ($campaignFromToken) {
                $query->where('campaign_id', $campaignFromToken);
            } else {
                // No campaign in token → show nothing
                $query->whereRaw('1 = 0');
            }
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
            'dial_method' => 'nullable|string',
        ]);

        $agent = LiveAgents::create($data);

        $this->refreshLoginTimesForToday();

        return $agent;
    }

    public function show($id)
    {
        $this->refreshLoginTimesForToday();

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

        $this->refreshLoginTimesForToday();

        return $liveAgent;
    }

    public function delete($id)
    {
        $liveAgent = LiveAgents::findOrFail($id);
        $liveAgent->delete();

        return ['message' => 'Live agent record deleted successfully'];
    }

    public function refreshLoginTimesForToday()
    {
        $today = now()->toDateString();

        $stats = DB::table('user_login_logs')
            ->select(
                'user_id',
                'login_date',
                DB::raw("SUM(TIMESTAMPDIFF(SECOND, CONCAT(login_date, ' ', login_time), CONCAT(logout_date, ' ', logout_time))) as total_seconds")
            )
            ->whereDate('login_date', $today)
            // ->whereNotNull('logout_time')
            ->groupBy('login_date', 'user_id')
            ->get();

        foreach ($stats as $stat) {
            LiveAgentDailyMetric::updateOrCreate(
                ['user_id' => $stat->user_id, 'date' => $stat->login_date],
                ['login_seconds' => $stat->total_seconds ?? 0]
            );
        }
        // return ['message' => 'Login durations updated for all users.'];
    }
}
