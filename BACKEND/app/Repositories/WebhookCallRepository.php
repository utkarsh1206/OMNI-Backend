<?php

namespace App\Repositories;

use App\Events\CallConnected;
use App\Events\CallHangup;
use App\Events\CallInitiated;
use App\Events\Conference\ConferenceAddPartyFailed;
use App\Events\Conference\ConferenceAddPartyInit;
use App\Events\Conference\ConferenceAddPartySuccessful;
use App\Events\Conference\ConferenceDisconnect;
use App\Events\Conference\ConferenceRemovePartySuccessful;
use App\Events\Conference\ConferenceStartFail;
use App\Events\Conference\ConferenceStartInit;
use App\Events\Conference\ConferenceStartSuccess;
use App\Events\ConferenceCallEvent;
use App\Models\NeoxCallLog;
use App\Models\NeoxTempCallLog;
use App\Models\ConferenceDetail;
use Carbon\Carbon;

class WebhookCallRepository
{
    // Save to neox_call_logs table
    public function saveCallLog($data)
    {
        return NeoxCallLog::create($data);
    }

    // Save to neox_temp_call_logs table

    public function saveTempCallLog($tempData, array $ids, $domain)
    {
        generateLogs('info', 'Temp-Data: ' . json_encode($tempData));

        $broadcastEvents = [
            'call_initiated' => CallInitiated::class,
            'call_connected' => CallConnected::class,
            'call_hangup'    => CallHangup::class,
        ];

        if ($this->shouldBroadcastToAgent($tempData, $ids, $broadcastEvents)) {
            broadcast(new $broadcastEvents[$tempData['type']]($tempData, $ids['agent_id'], $domain));
        }

        $confState = $tempData['conf_state'] ?? 'NONE';
        $isSotCall = isset($tempData['call_unique_id']) && strpos($tempData['call_unique_id'], 'SOT') === 0;

        $isParentSot = false;
        if (!$isSotCall) {
            $isParentSot = isset($tempData['parent_call_id']) && strpos($tempData['parent_call_id'], 'SOT') === 0;
        }

        $isPriorityState = in_array($confState, ['STARTCONF_SUCCESS', 'STARTCONF_FAIL']);

        if (!empty($ids['parent_id']) && ($isSotCall || $isParentSot || $isPriorityState)) {

            $stateMapping = [
                'ADDPARTY_SUCCESS'    => ConferenceAddPartySuccessful::class,
                'ADDPARTY_FAIL'       => ConferenceAddPartyFailed::class,
                'REMOVEPARTY_SUCCESS' => ConferenceRemovePartySuccessful::class,
                'STARTCONF_SUCCESS'   => ConferenceStartSuccess::class,
                'ADDPARTY_INIT'       => ConferenceAddPartyInit::class,
                'STARTCONF_INIT'      => ConferenceStartInit::class,    // DOESN'T SEEM REQUIRED
                'STARTCONF_FAIL'      => ConferenceStartFail::class,
                'DISCONNECTCONF'      => ConferenceDisconnect::class,
            ];

            $manualAccept = $tempData['manual_accept'] ?? false;

            // SOT + ADDPARTY_SUCCESS → broadcast only if manualAccept is true
            if ($confState === 'ADDPARTY_SUCCESS' && $manualAccept !== true) {
                return NeoxTempCallLog::create($tempData);
            }

            $eventClass = $stateMapping[$confState] ?? ConferenceCallEvent::class;

            broadcast(new $eventClass($tempData, $ids['parent_id'], $domain));
        }

        return NeoxTempCallLog::create($tempData);
    }

    private function shouldBroadcastToAgent(array $tempData, array $ids, array $broadcastEvents): bool
    {
        $agentIdPresent = !empty($ids['agent_id']);
        $typeValid = isset($broadcastEvents[$tempData['type']]);

        if (!$agentIdPresent || !$typeValid) {
            return false;
        }

        $isSot = isset($tempData['call_unique_id']) && strpos($tempData['call_unique_id'], 'SOT') === 0;
        $manualAccept = $tempData['manual_accept'] ?? false;

        // Non-SOT → always allow
        if (!$isSot) {
            return true;
        }

        // SOT handling
        if ($tempData['type'] === 'call_connected') {
            return $manualAccept === true;
        }

        // call_initiated & call_hangup → always allow
        return true;
    }

    // Update or create call history
    public function updateOrCreateCallHistory($data, $connection)
    {
        $now = Carbon::now();

        $existing = $connection->table('neox_call_logs')
            ->where('call_unique_id', $data['call_unique_id'])
            ->where('call_date', $data['call_date'])
            ->where('called_phone_number', $data['called_phone_number'])
            ->first();

        if ($existing) {
            // Update the existing record
            $connection->table('neox_call_logs')
                ->where('id', $existing->id)
                ->update($data);

            // Return the updated record
            $record = $connection->table('neox_call_logs')
                ->where('id', $existing->id)
                ->first();
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;

            // Insert new record and get inserted ID
            $insertedId = $connection->table('neox_call_logs')->insertGetId($data);

            // Return the inserted record
            $record = $connection->table('neox_call_logs')
                ->where('id', $insertedId)
                ->first();
        }

        return $record;
    }

    public function upsertConference(array $requestData, array $ids, string $type)
    {
        $callUniqueId = $requestData['call_unique_id'] ?? null;
        $userId = $ids['agent_id'];
        $extension = $requestData['caller_user_extension'];

        if (!$callUniqueId || !$userId || !$extension) {
            return null;
        }

        ConferenceDetail::where('user_id', $userId)
            ->where('caller_user_extension', $extension)
            ->where('call_unique_id', '!=', $callUniqueId)
            ->delete();

        return ConferenceDetail::updateOrCreate(
            [
                'call_unique_id' => $callUniqueId
            ],
            [
                'user_id'                      => $userId,
                'caller_username'              => $requestData['caller_username'] ?? null,
                'caller_user_extension'        => $extension,
                'parent_uid'                   => $ids['parent_id'],
                'conf_state'                   => $requestData['conf_state'] ?? null,
                'conf_event'                   => $type,
                'parent_user_id'               => $requestData['parent_user_id'] ?? null,
                'original_called_phone_number' => $requestData['called_phone_number'] ?? null,
                'actual_url_parameter'         => $requestData ?? null,
            ]
        );
    }

    public function deleteConference(string $callUniqueId)
    {
        if (!$callUniqueId) {
            return null;
        }

        return ConferenceDetail::where('call_unique_id', $callUniqueId)->delete();
    }
}
