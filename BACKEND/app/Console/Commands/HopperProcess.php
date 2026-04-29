<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Requests\CampaignApiRequest;
use App\Models\CallBack;
use App\Models\Detail;
use App\Models\EcdialCallTime;
use App\Models\EcdialHopper;
use App\Models\EcdialList;
use App\Models\EcdialLists;
use App\Models\EcdialStateCallTime;
use App\Models\NeoxConfiguration;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class HopperProcess extends Command
{
    protected $signature = 'app:hopper-process
        {--campaign= : Campaign ID} 
        {--level= : Dial level} 
        {--allow-inactive-list-leads : Allow inactive list leads} 
        {--wipe-hopper-clean : Wipe hopper clean}';

    protected $description = 'Process the dialer hopper with campaign leads';

    private CampaignProcessor $campaignProcessor;
    private CallbackProcessor $callbackProcessor;
    private TimeCalculator $timeCalculator;

    public function __construct()
    {
        parent::__construct();
        $this->campaignProcessor = new CampaignProcessor();
        $this->callbackProcessor = new CallbackProcessor();
        $this->timeCalculator = new TimeCalculator();
    }

    public function handle()
    {
        generateScheduleLogs('info', 'Hopper Process started for all tenants.');
        $tenantDetails = Detail::select('domain', 'uin')->get()->keyBy('domain');

        Tenant::orderBy('id')
            ->chunkById(100, function ($tenants) use ($tenantDetails) {
                foreach ($tenants as $tenant) {
                    try {
                        generateScheduleLogs('info', 'Hopper Process started for all tenants.' . $tenant->domain_name);
                        $match = $tenantDetails->get($tenant->domain_name);

                        if ($match) {
                            $tenantUin = $match->uin;
                        } else {
                            generateScheduleLogs('warning', 'No UIN found for ' . $tenant->domain_name);
                            continue;
                        }

                        $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                        $databaseName = str_replace(".", "_", $tenant->domain_name);
                        establishDatabaseConnect($newConnectionName, $databaseName);
                        DB::reconnect($newConnectionName);

                        $domain_name = $tenant->domain_name;

                        $options = $this->getOptionsData();

                        if ($options['wipe_hopper']) {
                            return $this->wipeHopper($options['campaign']);
                        }

                        $this->cleanupInactiveLists($newConnectionName, $options['allow_inactive']);
                        $this->callbackProcessor->processExpiredCallbacks($newConnectionName, $this->timeCalculator->getCurrentTime());

                        $campaigns = $this->getCampaigns($newConnectionName, $options['campaign'], $domain_name, $tenantUin);

                        foreach ($campaigns as $campaign) {
                            $this->campaignProcessor->processCampaign($newConnectionName, $campaign, $options);
                        }
                    } catch (\Illuminate\Database\QueryException $e) {

                        generateScheduleLogs(
                            'error',
                            "Skipping tenant {$tenant->domain_name} - DB/Table issue: " . $e->getMessage()
                        );

                        continue; // 🔥 SKIP TENANT

                    } catch (\PDOException $e) {

                        generateScheduleLogs(
                            'error',
                            "Skipping tenant {$tenant->domain_name} - PDO issue: " . $e->getMessage()
                        );

                        continue;
                    } catch (\Throwable $e) {

                        generateScheduleLogs(
                            'error',
                            "Skipping tenant {$tenant->domain_name} - Unexpected error: " . $e->getMessage()
                        );

                        continue;
                    }
                }
                // return Command::SUCCESS;
            });
        generateScheduleLogs('info', 'Hopper Process completed for all tenants.');
    }

    private function getOptionsData()
    {
        return [
            'campaign' => $this->option('campaign'),
            'level' => $this->cleanLevel($this->option('level')),
            'allow_inactive' => $this->option('allow-inactive-list-leads'),
            'wipe_hopper' => $this->option('wipe-hopper-clean'),
        ];
    }

    private function cleanLevel(?string $level): ?string
    {
        return $level ? preg_replace('/\D/', '', $level) : null;
    }

    private function wipeHopper(?string $campaign): int
    {
        $query = $campaign
            ? EcdialHopper::where('campaign_id', $campaign)
            : EcdialHopper::query();

        $affectedRows = $query->delete();

        $this->info("Hopper cleaned successfully. Rows deleted: $affectedRows");
        return Command::SUCCESS;
    }

    private function cleanupInactiveLists($newConnectionName, $allowInactive)
    {
        if ($allowInactive) {
            return;
        }
        $inactiveListIds = EcdialLists::on($newConnectionName)->where('status', 'inactive')->pluck('list_id');

        if ($inactiveListIds->isNotEmpty()) {
            EcdialHopper::on($newConnectionName)->whereIn('list_id', $inactiveListIds)->delete();
        }
    }

    private function getCampaigns($newConnectionName, $campaignId = null, $domainName, $tenantUin)
    {
        try {
            # NOC-955 TODO Hosted Multitenant Start
            if (config('omni.IS_DC_MULTITENANT') == 1) {

                $lowerDomain = strToLower($domainName);

                $token = $tenantUin;
                $ip = rtrim(config('omni.HOSTED_URL'), '/');
                $url = "{$ip}/api/admin/actions/v2/campaign_list.php?domain={$lowerDomain}";

                $postData = [
                    "token" => $token,
                ];

                $response = sendHttpRequest($url, 'POST', $postData);
            }   # NOC-955 TODO Hosted Multitenant END
            else {
                $config = NeoxConfiguration::on($newConnectionName)->first(['neox_local_ip', 'neox_token']);

                if (isset($config['status']) && $config['status'] === 'error') {
                    generateScheduleLogs("error", "Config error");
                    return [];
                }
                $ip = $config['neox_local_ip'];
                $url = "https://{$ip}/api/admin/actions/v2/campaign_list.php";

                $payload = [
                    'domain_name' => $domainName,
                    'isJWT' => 'No'
                ];

                $response = sendHttpRequest($url, 'POST', $payload);
            }

            $apiData = json_decode($response->getContent(), true);

            if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                return response()->json($apiData);
            }

            if (($apiData['code'] ?? -1) !== 0) {
                generateScheduleLogs("error", "API returned error: " . ($apiData['message'] ?? 'Unknown'));
                return [];
            }

            $apiCampaigns = $apiData['data'] ?? [];

            if ($campaignId) {
                $apiCampaigns = array_filter(
                    $apiCampaigns,
                    fn($c) => ($c['campaign_id'] ?? null) === $campaignId
                );
            }

            return array_map(fn($c) => (object) $c, $apiCampaigns);
        } catch (\Exception $e) {
            generateScheduleLogs("error", "Exception: " . $e->getMessage());
            return [];
        }
    }
}

class CampaignProcessor
{
    private TimeCalculator $timeCalculator;
    private CallTimeProcessor $callTimeProcessor;
    private LeadProcessor $leadProcessor;

    public function __construct()
    {
        $this->timeCalculator = new TimeCalculator();
        $this->callTimeProcessor = new CallTimeProcessor();
        $this->leadProcessor = new LeadProcessor();
    }

    public function processCampaign($newConnectionName, $campaign, array $options): void
    {
        if ($campaign->no_hopper_dialing === 'Y') {
            $this->handleNoHopperDialing($newConnectionName, $campaign->campaign_id);
            return;
        }

        $hopperLevel = $options['level'] ?: $campaign->hopper_level;
        $callTimeSettings = $this->callTimeProcessor->getSettings($newConnectionName, $campaign->local_call_time);
        $gmtData = $this->timeCalculator->calculateGmtData();

        $this->cleanupCampaignHopper($newConnectionName, $campaign->campaign_id);

        // Process DNC entries before checking ready count
        $this->processDncEntries($newConnectionName, $campaign);

        $readyCount = $this->getReadyHopperCount($newConnectionName, $campaign->campaign_id);

        if ($readyCount < $hopperLevel) {
            $this->leadProcessor->addLeadsToHopper($newConnectionName, $campaign, $hopperLevel, $callTimeSettings, $gmtData, $options);
        }

        $this->updateCampaignStats($campaign);
    }

    private function processDncEntries($newConnectionName, $campaign): void
    {
        // Count DNC entries in hopper
        $hopperDncCount = EcdialHopper::on($newConnectionName)->where('campaign_id', $campaign->campaign_id)
            ->where('status', 'DNC')
            ->count();

        // $eventString = "Hopper - Information, DNC count: $hopperDncCount";
        // generateScheduleLogs('info', $eventString);

        if ($hopperDncCount > 0) {
            // Get all DNC statused hopper entries
            $dncEntries = EcdialHopper::on($newConnectionName)->where('campaign_id', $campaign->campaign_id)
                ->where('status', 'DNC')
                ->select(['hopper_id', 'lead_id', 'alt_dial', 'campaign_id'])
                ->get();

            // $eventString = "Hopper - Information, Found " . $dncEntries->count() . " DNC entries";
            // generateScheduleLogs('info', $eventString);

            foreach ($dncEntries as $entry) {
                $this->processDncEntry($newConnectionName, $entry, $campaign);
            }
        }
    }

    private function processDncEntry($newConnectionName, $dncEntry, object $campaign): void
    {
        $autoAltDial = $campaign->auto_alt_dial ?? '';
        $useInternalDnc = $campaign->use_internal_dnc ?? '';
        $useCampaignDnc = $campaign->use_campaign_dnc ?? '';
        $currentAltDial = $dncEntry->alt_dial;

        // $eventString = "Hopper - Information, Auto Dial Level: $autoAltDial, use internal DNC: $useInternalDnc";
        // generateScheduleLogs('debug', $eventString);

        // Process ALT phone number
        if (
            preg_match('/(ALT_ONLY|ALT_AND_ADDR3|ALT_AND_EXTENDED)/', $autoAltDial) &&
            in_array($currentAltDial, ['NONE', 'MAIN'])
        ) {

            $this->processAltPhone($newConnectionName, $dncEntry, $campaign, $autoAltDial, $useInternalDnc);
        }

        // Process ADDR3 phone number
        if ((preg_match('/(ADDR3_ONLY)/', $autoAltDial) && in_array($currentAltDial, ['NONE', 'MAIN'])) ||
            (preg_match('/(ALT_AND_ADDR3)/', $autoAltDial) && $currentAltDial === 'ALT')
        ) {

            $this->processAddr3Phone($newConnectionName, $dncEntry, $campaign, $autoAltDial, $useInternalDnc);
        }
    }

    private function processAltPhone($newConnectionName, $dncEntry, object $campaign, string $autoAltDial, string $useInternalDnc): void
    {
        // $eventString = "Hopper - Information, Processing ALT phone for lead: {$dncEntry->lead_id}";
        // generateScheduleLogs('trace', $eventString);

        $leadDetails = EcdialList::on($newConnectionName)->where('lead_id', $dncEntry->lead_id)
            ->select(['alt_phone', 'gmt_offset_now', 'list_id'])
            ->first();

        if (!$leadDetails) {
            return;
        }

        $altPhone = preg_replace('/\D/', '', $leadDetails->alt_phone ?? '');

        if (strlen($altPhone) > 5) {
            $altDncCount = 0;

            if ($useInternalDnc === 'Y') {
                // $eventString = "Hopper - Information, Checking DNC for ALT phone: $altPhone";
                // generateScheduleLogs('debug', $eventString);

                $altDncCount = 0;

                // $eventString = "Hopper - Information, DNC count for ALT phone: $altDncCount";
                // generateScheduleLogs('trace', $eventString);
            }

            if ($useInternalDnc !== 'Y' || $altDncCount < 1) {
                EcdialHopper::on($newConnectionName)->where('hopper_id', $dncEntry->hopper_id)
                    ->update([
                        'status' => 'READY',
                        'list_id' => $leadDetails->list_id,
                        'gmt_offset_now' => $leadDetails->gmt_offset_now,
                        // 'state' => $leadDetails->state,
                        'alt_dial' => 'ALT',
                        'user' => '',
                        'priority' => 25
                    ]);

                // $eventString = "Hopper - Information, Updated hopper entry to READY for ALT phone";
                // generateScheduleLogs('debug', $eventString);
            } else {
                // Skip this alt dial
                $dncEntry->alt_dial = 'ALT';
            }
        } else {
            // Skip this alt dial
            $dncEntry->alt_dial = 'ALT';
        }
    }

    private function processAddr3Phone($newConnectionName, $dncEntry, object $campaign, string $autoAltDial, string $useInternalDnc): void
    {
        // $eventString = "Hopper - Information, Processing ADDR3 phone for lead: {$dncEntry->lead_id}";
        // generateScheduleLogs('trace', $eventString);

        $leadDetails = EcdialList::on($newConnectionName)->where('lead_id', $dncEntry->lead_id)
            ->select(['address3', 'gmt_offset_now', 'state', 'list_id'])
            ->first();

        if (!$leadDetails) {
            return;
        }

        $address3 = preg_replace('/\D/', '', $leadDetails->address3 ?? '');

        // $eventString = "Hopper - Information, ADDR3 phone: $address3";
        // generateScheduleLogs('debug', $eventString);

        if (strlen($address3) > 5) {
            $altDncCount = 0;

            if ($useInternalDnc === 'Y') {
                // $eventString = "Hopper - Information, Checking DNC for ADDR3 phone: $address3";
                // generateScheduleLogs('trace', $eventString);

                $altDncCount = 0;

                // $eventString = "Hopper - Information, DNC count for ADDR3 phone: $altDncCount";
                // generateScheduleLogs('trace', $eventString);
            }

            if ($useInternalDnc !== 'Y' || $altDncCount < 1) {
                EcdialHopper::on($newConnectionName)->where('hopper_id', $dncEntry->hopper_id)
                    ->update([
                        'status' => 'READY',
                        'list_id' => $leadDetails->list_id,
                        'gmt_offset_now' => $leadDetails->gmt_offset_now,
                        // 'state' => $leadDetails->state,
                        'alt_dial' => 'ADDR3',
                        'user' => '',
                        'priority' => 25
                    ]);

                // $eventString = "Hopper - Information, Updated hopper entry to READY for ADDR3 phone";
                // generateScheduleLogs('debug', $eventString);
            } else {
                // Skip this addr3 dial
                $dncEntry->alt_dial = 'ADDR3';
            }
        } else {
            // Skip this addr3 dial
            $dncEntry->alt_dial = 'ADDR3';
        }
    }

    private function handleNoHopperDialing($newConnectionName, $campaignId): void
    {
        $count = EcdialHopper::on($newConnectionName)->where('campaign_id', $campaignId)
            ->whereNotIn('status', ['QUEUE', 'HOLD'])
            ->count();

        if ($count > 0) {
            EcdialHopper::on($newConnectionName)->where('campaign_id', $campaignId)
                ->whereNotIn('status', ['QUEUE', 'HOLD'])
                ->delete();
        }
    }

    private function cleanupCampaignHopper($newConnectionName, $campaignId): void
    {
        EcdialHopper::on($newConnectionName)->where('campaign_id', $campaignId)
            ->whereIn('status', ['DONE'])
            ->delete();
    }

    private function getReadyHopperCount($newConnectionName, $campaignId): int
    {
        return EcdialHopper::on($newConnectionName)->where('campaign_id', $campaignId)
            ->where('status', 'READY')
            ->count();
    }

    private function updateCampaignStats(object $campaign): void
    {
        // Implementation for updating campaign statistics
        // This would contain the logic from the original code for updating dialable leads count
    }
}

class CallbackProcessor
{
    public function processExpiredCallbacks($newConnectionName, Carbon $now): void
    {
        $expiredCallbacks = CallBack::on($newConnectionName)
            ->where('call_back_time', '<=', $now)
            ->where('lead_status', 'active')
            ->count();

        if ($expiredCallbacks <= 0) {
            return;
        }

        DB::beginTransaction();
        try {
            $this->activateExpiredCallbacks($newConnectionName, $now);
            $this->processAnyoneCallbacks($newConnectionName, $now);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            generateScheduleLogs("error", "Exception: " . $e->getMessage());
        }
    }

    private function activateExpiredCallbacks($newConnectionName, Carbon $now): void
    {
        CallBack::on($newConnectionName)
            ->where('call_back_time', '<=', $now)
            ->where('lead_status', 'active')
            ->whereNotIn('lead_status', ['inactive', 'dead', 'archive'])
            ->update(['lead_status' => 'live']);
    }

    private function processAnyoneCallbacks($newConnectionName, Carbon $now): void
    {
        $callbacks = CallBack::on($newConnectionName)
            ->join('ecdial_list', 'ecdial_list.lead_id', '=', 'call_backs.lead_id')
            ->where('call_backs.call_back_time', '<=', $now)
            ->where('call_backs.lead_status', 'live')
            ->whereRaw("call_backs.recipient LIKE '%Anyone%'")
            ->select(
                'call_backs.lead_id',
                'call_backs.recipient',
                'call_backs.campaign_id',
                'call_backs.list_id',
                'ecdial_list.gmt_offset_now',
                'call_backs.lead_status'
            )
            ->get();

        foreach ($callbacks as $callback) {
            $this->addCallbackToHopper($newConnectionName, $callback, $now);
        }
    }

    private function addCallbackToHopper($newConnectionName, $callback, Carbon $now): void
    {
        EcdialList::on($newConnectionName)
            ->where('lead_id', $callback->lead_id)
            ->where('called_since_last_reset', '!=', 'N')
            ->update([
                // 'status' => $callback->lead_status,
                'called_since_last_reset' => 'N',
                'last_dialable_time' => $now
            ]);

        EcdialHopper::on($newConnectionName)->insert([
            'lead_id'         => $callback->lead_id,
            'campaign_id'     => $callback->campaign_id,
            'list_id'         => $callback->list_id,
            'priority'        => 50,
            'source'          => 'C',
        ]);
    }
}

class TimeCalculator
{
    public function getCurrentTime(): Carbon
    {
        return Carbon::now(config('app.timezone'));
    }

    public function calculateGmtData(): array
    {
        $gmtData = [];
        $now = $this->getCurrentTime();
        $gmtNow = $now->copy()->setTimezone('UTC');

        $offset = -13.0;
        $index = 0;

        while ($offset <= 13.0) {
            $time = $gmtNow->copy()->addHours($offset);

            $gmtData[] = [
                'offset' => number_format($offset, 2, '.', ''),
                'day' => $time->dayOfWeek,
                'hour' => ($time->hour * 100) + $time->minute,
            ];

            $offset += 0.25;
            $index++;
        }

        return $gmtData;
    }
}

class CallTimeProcessor
{
    public function getSettings($newConnectionName, $callTimeId): ?array
    {
        $callTime = EcdialCallTime::on($newConnectionName)->where('call_time_id', $callTimeId)->first();

        if (!$callTime) {
            return null;
        }

        return [
            'default_start' => $callTime->ct_default_start,
            'default_stop' => $callTime->ct_default_stop,
            'sunday_start' => $callTime->ct_sunday_start,
            'sunday_stop' => $callTime->ct_sunday_stop,
            'monday_start' => $callTime->ct_monday_start,
            'monday_stop' => $callTime->ct_monday_stop,
            'tuesday_start' => $callTime->ct_tuesday_start,
            'tuesday_stop' => $callTime->ct_tuesday_stop,
            'wednesday_start' => $callTime->ct_wednesday_start,
            'wednesday_stop' => $callTime->ct_wednesday_stop,
            'thursday_start' => $callTime->ct_thursday_start,
            'thursday_stop' => $callTime->ct_thursday_stop,
            'friday_start' => $callTime->ct_friday_start,
            'friday_stop' => $callTime->ct_friday_stop,
            'saturday_start' => $callTime->ct_saturday_start,
            'saturday_stop' => $callTime->ct_saturday_stop,
            'state_call_times' => $callTime->ct_state_call_times,
        ];
    }

    public function processStateCallTimes($newConnectionName, $stateCallTimes, array $gmtData): array
    {
        if (empty($stateCallTimes)) {
            return ['', ''];
        }

        $stateRules = explode('|', $stateCallTimes);
        $ctStates = [];
        $stateGmtSql = '';
        $delStateGmtSql = '';

        foreach ($stateRules as $stateRuleId) {
            $stateCallTime = EcdialStateCallTime::on($newConnectionName)->where('state_call_time_id', $stateRuleId)->first();

            if ($stateCallTime) {
                $processed = $this->processSingleStateCallTime($stateCallTime, $gmtData);

                $ctStates[] = "'{$stateCallTime->state_call_time_state}'";
                $stateGmtSql .= "or (state='{$stateCallTime->state_call_time_state}' AND gmt_offset_now IN({$processed['state_gmt']})) ";
                $delStateGmtSql .= "or (state='{$stateCallTime->state_call_time_state}' AND gmt_offset_now IN({$processed['del_state_gmt']})) ";
            }
        }

        $ctStatesSQL = !empty($ctStates) ? "AND state NOT IN(" . implode(',', $ctStates) . ")" : '';

        return [
            "(gmt_offset_now IN('99') $ctStatesSQL) $stateGmtSql",
            "(gmt_offset_now IN('99') $ctStatesSQL) $delStateGmtSql"
        ];
    }

    private function processSingleStateCallTime(object $stateCallTime, array $gmtData): array
    {
        $stateGmt = '';
        $delStateGmt = '';

        foreach ($gmtData as $gmt) {
            $isWithinHours = $this->isWithinCallTime(
                $gmt['hour'],
                $gmt['day'],
                $stateCallTime
            );

            if ($isWithinHours) {
                $stateGmt .= "'{$gmt['offset']}',";
            } else {
                $delStateGmt .= "'{$gmt['offset']}',";
            }
        }

        return [
            'state_gmt' => $stateGmt . "'99'",
            'del_state_gmt' => $delStateGmt . "'99'",
        ];
    }

    private function isWithinCallTime(int $hour, int $day, object $stateCallTime): bool
    {
        $settings = $this->getDaySettings($day, $stateCallTime);

        if ($settings['start'] == 0 && $settings['stop'] == 0) {
            return $hour >= $stateCallTime->sct_default_start && $hour < $stateCallTime->sct_default_stop;
        }

        return $hour >= $settings['start'] && $hour < $settings['stop'];
    }

    private function getDaySettings(int $day, object $stateCallTime): array
    {
        return match ($day) {
            0 => ['start' => $stateCallTime->sct_sunday_start, 'stop' => $stateCallTime->sct_sunday_stop],
            1 => ['start' => $stateCallTime->sct_monday_start, 'stop' => $stateCallTime->sct_monday_stop],
            2 => ['start' => $stateCallTime->sct_tuesday_start, 'stop' => $stateCallTime->sct_tuesday_stop],
            3 => ['start' => $stateCallTime->sct_wednesday_start, 'stop' => $stateCallTime->sct_wednesday_stop],
            4 => ['start' => $stateCallTime->sct_thursday_start, 'stop' => $stateCallTime->sct_thursday_stop],
            5 => ['start' => $stateCallTime->sct_friday_start, 'stop' => $stateCallTime->sct_friday_stop],
            6 => ['start' => $stateCallTime->sct_saturday_start, 'stop' => $stateCallTime->sct_saturday_stop],
            default => ['start' => $stateCallTime->sct_default_start, 'stop' => $stateCallTime->sct_default_stop],
        };
    }
}

class LeadProcessor
{
    public function addLeadsToHopper($newConnectionName, $campaign, int $hopperLevel, ?array $callTimeSettings, array $gmtData, array $options): void
    {
        $existingLeadIds = $this->getExistingHopperLeadIds($newConnectionName, $campaign->campaign_id);
        $campaignLists = $this->getCampaignLists($newConnectionName, $campaign->campaign_id, $options['allow_inactive']);

        if (empty($campaignLists)) {
            return;
        }

        $leads = $this->getEligibleLeads($newConnectionName, $campaign, $campaignLists, $existingLeadIds, $hopperLevel, $callTimeSettings, $gmtData);
        $this->insertLeadsIntoHopper($newConnectionName, $leads, $campaign->campaign_id);
    }

    private function getExistingHopperLeadIds($newConnectionName, $campaignId): array
    {
        return EcdialHopper::on($newConnectionName)->where('campaign_id', $campaignId)
            ->pluck('lead_id')
            ->toArray() ?: [0];
    }

    private function getCampaignLists($newConnectionName, $campaignId, bool $allowInactive): array
    {
        $query = EcdialLists::on($newConnectionName)->where('campaign_id', $campaignId);

        if (!$allowInactive) {
            $query->where('status', 'active');
        }

        return $query->pluck('list_id')->toArray();
    }

    private function getEligibleLeads($newConnectionName, $campaign, array $listIds, array $existingLeadIds, int $limit, ?array $callTimeSettings, array $gmtData): array
    {
        $query = EcdialList::on($newConnectionName)
            ->select([
                'lead_id',
                'list_id',
                'gmt_offset_now',
                // 'phone_number',
                // 'state',
                'status',
                'modify_date',
                'user'
            ])
            ->where('called_since_last_reset', 'N')
            ->whereIn('list_id', $listIds)
            ->whereNotIn('lead_id', $existingLeadIds)
            ->limit($limit);

        $this->applyStatusConditions($query, $campaign);
        $this->applyCallTimeConditions($newConnectionName, $query, $callTimeSettings, $gmtData);
        $this->applyDropLockoutConditions($query, $campaign);
        $this->applyOrdering($query, $campaign);

        return $query->get()->toArray();
    }

    private function applyStatusConditions($query, object $campaign): void
    {
        if (str_contains($campaign->list_order_mix, 'DISABLED')) {
            $statuses = collect(explode(' ', trim($campaign->dial_statuses)))
                ->filter(fn($s) => !empty($s))
                ->map(fn($s) => "'$s'")
                ->implode(',');

            $query->whereRaw("status IN($statuses)");
        }
    }

    private function applyCallTimeConditions($newConnectionName, $query, ?array $callTimeSettings, array $gmtData): void
    {
        if ($callTimeSettings && !empty($callTimeSettings['state_call_times'])) {
            $callTimeProcessor = new CallTimeProcessor();
            [$gmtSql] = $callTimeProcessor->processStateCallTimes($newConnectionName, $callTimeSettings['state_call_times'], $gmtData);
            $query->whereRaw("($gmtSql)");
        }
    }

    private function applyDropLockoutConditions($query, object $campaign): void
    {
        if ($campaign->drop_lockout_time > 0) {
            $seconds = $campaign->drop_lockout_time * 3600;
            $query->whereRaw(
                "((status IN('DROP','XDROP') AND last_local_call_time < DATE_ADD(NOW(), INTERVAL -$seconds SECOND)) OR status NOT IN('DROP','XDROP'))"
            );
        }
    }

    private function applyOrdering($query, object $campaign): void
    {
        $orderBuilder = new LeadOrderBuilder($campaign);
        $orderBuilder->applyOrdering($query);
    }

    private function insertLeadsIntoHopper($newConnectionName, $leads, string $campaignId): void
    {
        $hopperData = [];

        $chunkSize = 1000;
        foreach ($leads as $lead) {
            $hopperData[] = [
                'lead_id' => $lead['lead_id'],
                'campaign_id' => $campaignId,
                'list_id' => $lead['list_id'],
                'gmt_offset_now' => $lead['gmt_offset_now'],
                // 'phone_number' => $lead->phone_number,
                // 'state' => $lead->state,
                'status' => 'READY',
                'user' => $lead['user'],
                'source' => 'S',
                'priority' => 20,
            ];
        }

        if (!empty($hopperData)) {
            foreach (array_chunk($hopperData, $chunkSize) as $chunk) {
                EcdialHopper::on($newConnectionName)->insert($chunk);
            }
        }
    }
}

class LeadOrderBuilder
{
    public function __construct(private object $campaign) {}

    public function applyOrdering($query): void
    {
        if ($this->campaign->lead_order_randomize === 'Y') {
            $query->orderByRaw('RAND()');
            return;
        }

        $this->applyPrimaryOrdering($query);
        $this->applySecondaryOrdering($query);
    }

    private function applyPrimaryOrdering($query): void
    {
        $orderMap = [
            '/^DOWN/' => [['last_dialable_time', 'asc'], ['lead_id', 'asc']],
            '/^UP/' => [['last_dialable_time', 'desc'], ['lead_id', 'desc']],
            '/^UP LAST NAME/' => [['last_name', 'desc']],
            '/^DOWN LAST NAME/' => [['last_name', 'asc']],
            '/^UP PHONE/' => [['phone_number', 'desc']],
            '/^DOWN PHONE/' => [['phone_number', 'asc']],
            '/^UP COUNT/' => [['called_count', 'desc']],
            '/^DOWN COUNT/' => [['called_count', 'asc']],
            '/^UP LAST CALL TIME/' => [['last_local_call_time', 'desc']],
            '/^DOWN LAST CALL TIME/' => [['last_local_call_time', 'asc']],
            '/^RANDOM/' => [DB::raw('RAND()')],
            '/^UP RANK/' => [['rank', 'desc']],
            '/^DOWN RANK/' => [['rank', 'asc']],
            '/^UP OWNER/' => [['owner', 'desc']],
            '/^DOWN OWNER/' => [['owner', 'asc']],
            '/^UP TIMEZONE/' => [['gmt_offset_now', 'desc']],
            '/^DOWN TIMEZONE/' => [['gmt_offset_now', 'asc']],
        ];

        foreach ($orderMap as $pattern => $orders) {
            if (preg_match($pattern, $this->campaign->lead_order)) {
                foreach ($orders as $order) {
                    if (is_array($order)) {
                        $query->orderBy($order[0], $order[1]);
                    } else {
                        $query->orderByRaw($order);
                    }
                }
                break;
            }
        }
    }

    private function applySecondaryOrdering($query): void
    {
        $secondaryOrder = match (true) {
            preg_match('/LEAD_ASCEND/', $this->campaign->lead_order_secondary) => 'lead_id asc',
            preg_match('/LEAD_DESCEND/', $this->campaign->lead_order_secondary) => 'lead_id desc',
            preg_match('/CALLTIME_ASCEND/', $this->campaign->lead_order_secondary) => 'last_local_call_time asc',
            preg_match('/CALLTIME_DESCEND/', $this->campaign->lead_order_secondary) => 'last_local_call_time desc',
            default => 'lead_id asc',
        };

        $query->orderByRaw($secondaryOrder);
    }
}
