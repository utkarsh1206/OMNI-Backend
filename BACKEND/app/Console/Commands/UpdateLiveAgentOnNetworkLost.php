<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\LiveAgents;
use App\Models\UserDailyActivity;
use App\Models\UserLoginLog;
use App\Services\ApiRequestService;
use App\Http\Requests\LogoutApiRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateLiveAgentOnNetworkLost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-live-agent-on-network-lost {uin?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the live agent from the database if network is lost';

    private $apiRequestService;

    public function __construct(ApiRequestService $apiRequestService)
    {
        parent::__construct();
        $this->apiRequestService = $apiRequestService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        generateScheduleLogs('info', 'Remove the live agent from the database if network is lost Started.');
        getDbConnection($this->argument("uin"), true);
        $this->updateLiveAgents();
        // Tenant::orderBy('id')
        //     ->chunkById(100, function ($tenants) {
        //         foreach ($tenants as $tenant) {
        //             $newConnectionName = str_replace(".", "_", $tenant->domain_name);
        //             getDbConnection($tenant->domain_name);
        //             DB::reconnect($newConnectionName);
        //             $this->updateLiveAgents($newConnectionName);
        //         }
        //     });
        generateScheduleLogs('info', 'Remove the live agent from the database if network is lost completed.');
        return Command::SUCCESS;
    }

    public function updateLiveAgents()
    {
        try {
            $thresholdTime = Carbon::now()->subHour();

            $staleAgents = LiveAgents::with('user')
                ->where('agent_last_activity', '<', $thresholdTime)
                ->get();
            $count = $staleAgents->count();

            if ($count > 0) {
                foreach ($staleAgents as $agent) {
                    $data = [
                        'user_id' => $agent->user->username,
                        'campaign_id' => $agent->campaign_id,
                        'id' => $agent->user_id,
                    ];
                    $this->removeUserFromDialCenter($data);
                    $this->info("Deleting LiveAgent ID {$agent->id} - Last updated at: {$agent->agent_last_activity}");
                    $agent->delete();
                }

                $this->info("Deleted $count LiveAgent(s) inactive for more than 1 hour.");
            } else {
                $this->info("No inactive LiveAgents found for deletion.");
            }
        } catch (Exception $e) {
            generateLogs('error', 'Error deleting inactive LiveAgents: ', $e->getMessage());
        }
    }

    private function removeUserFromDialCenter($userInfo)
    {
        try {
            $request = new Request($userInfo);

            $result = $this->apiRequestService->handleApiRequest(
                $request,
                'agent_logout',
                LogoutApiRequest::class,
            );

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $result = $result->getData(true);
            }

            if (isset($result['code']) && $result['code'] === 0) {
                $this->updateUserLoginLog($request->id, $request->campaign_id);
                $duration = $this->calculateLoginDuration($request->id, $request->campaign_id);

                $activityData = [
                    'campaign_id' => $request->campaign_id,
                    'user_id' => $request->id,
                    'activity' => 'Logout',
                    'total_login_duration' => $duration,
                ];

                UserDailyActivity::create($activityData);

                return (response()->json($result))->original + ['neox_status' => true];
            }

            return $result;
        } catch (Exception $e) {
            generateLogs('error', 'Logout processing failed: ', $e->getMessage());
            return null;
        }
    }

    private function updateUserLoginLog($userId, $campaignId)
    {
        $latestLog = UserLoginLog::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->orderByDesc('login_date')
            ->orderByDesc('login_time')
            ->first();

        if ($latestLog) {
            $latestLog->update([
                'logout_date' => Carbon::today()->toDateString(),
                'logout_time' => Carbon::now()->toTimeString(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function calculateLoginDuration($userId, $campaignId)
    {
        $loginActivity = UserDailyActivity::where('user_id', $userId)
            ->where('campaign_id', $campaignId)
            ->where('activity', 'Login')
            ->whereDate('created_at', Carbon::today())
            ->orderByDesc('id')
            ->first();

        if ($loginActivity) {
            $diffInSeconds = Carbon::parse($loginActivity->created_at)->diffInSeconds(Carbon::now());

            return gmdate('H:i:s', $diffInSeconds);
        }

        return null;
    }
}
