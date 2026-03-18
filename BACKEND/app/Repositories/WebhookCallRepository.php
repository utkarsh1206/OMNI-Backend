<?php

namespace App\Repositories;

use App\Events\CallConnected;
use App\Events\CallHangup;
use App\Events\CallInitiated;
use App\Events\Conference\ConferenceAddPartyFailed;
use App\Events\Conference\ConferenceAddPartyInit;
use App\Events\Conference\ConferenceAddPartySuccessful;
use App\Events\Conference\ConferenceRemovePartySuccessful;
use App\Events\Conference\ConferenceStartFail;
use App\Events\Conference\ConferenceStartInit;
use App\Events\Conference\ConferenceStartSuccess;
use App\Events\ConferenceCallEvent;
use App\Models\NeoxCallLog;
use App\Models\NeoxTempCallLog;
use App\Events\DialCenterData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WebhookCallRepository
{
    // Save to neox_call_logs table
    public function saveCallLog($data)
    {
        return NeoxCallLog::create($data);
    }

    // Save to neox_temp_call_logs table
    // public function saveTempCallLog($tempData, $id, $domain)
    // {
    //     generateLogs('info', 'Temp-Data: ' . json_encode($tempData));

    //     if (isset($tempData['call_unique_id']) && strpos($tempData['call_unique_id'], 'SOT') === 0) {

    //         $stateMapping = [
    //             'ADDPARTY_SUCCESS'    => ConferenceAddPartySuccessful::class,
    //             'ADDPARTY_FAIL'       => ConferenceAddPartyFailed::class,
    //             'REMOVEPARTY_SUCCESS' => ConferenceRemovePartySuccessful::class,
    //             'STARTCONF_SUCCESS'   => ConferenceStartSuccess::class,
    //             'ADDPARTY_INIT'       => ConferenceAddPartyInit::class,
    //             'STARTCONF_INIT'      => ConferenceStartInit::class,
    //             'STARTCONF_FAIL'      => ConferenceStartFail::class,
    //         ];

    //         $confState = $tempData['conf_state'] ?? 'NONE';

    //         $eventClass = $stateMapping[$confState] ?? ConferenceCallEvent::class;
    //         // broadcast(new ConferenceCallEvent($tempData, $id, $domain));
    //         // Broadcast to the NEW conference channel
    //         broadcast(new $eventClass($tempData, $id, $domain));
    //     } else {

    //         $broadcastEvents = [
    //             'call_initiated' => CallInitiated::class,
    //             'call_connected' => CallConnected::class,
    //             'call_hangup'    => CallHangup::class,
    //         ];

    //         if (isset($broadcastEvents[$tempData['type']])) {
    //             broadcast(new $broadcastEvents[$tempData['type']]($tempData, $id, $domain));
    //         }
    //     }
    //     // broadcast(new DialCenterData($tempData));
    //     return NeoxTempCallLog::create($tempData);
    // }

    public function saveTempCallLog($tempData, array $ids, $domain)
    {
        generateLogs('info', 'Temp-Data: ' . json_encode($tempData));

        $broadcastEvents = [
            'call_initiated' => CallInitiated::class,
            'call_connected' => CallConnected::class,
            'call_hangup'    => CallHangup::class,
        ];

        if ($ids['agent_id'] && isset($broadcastEvents[$tempData['type']])) {
            // broadcast(new $broadcastEvents[$tempData['type']]($tempData, $ids['agent_id'], $domain));
            $isSot = (isset($tempData['call_unique_id']) && strpos($tempData['call_unique_id'], 'SOT') === 0);

            $confState = $tempData['conf_state'] ?? null;
            $invalidStates = [null, '', 'none', 'NONE', 'None'];
            $hasNoConfState = in_array($confState, $invalidStates, true);

            if (!($isSot && $hasNoConfState)) {
                broadcast(new $broadcastEvents[$tempData['type']]($tempData, $ids['agent_id'], $domain));
            }
        }

        if ($ids['parent_id'] && isset($tempData['call_unique_id']) && strpos($tempData['call_unique_id'], 'SOT') === 0) {
            
            $stateMapping = [
                'ADDPARTY_SUCCESS'    => ConferenceAddPartySuccessful::class,
                'ADDPARTY_FAIL'       => ConferenceAddPartyFailed::class,
                'REMOVEPARTY_SUCCESS' => ConferenceRemovePartySuccessful::class,
                'STARTCONF_SUCCESS'   => ConferenceStartSuccess::class,
                'ADDPARTY_INIT'       => ConferenceAddPartyInit::class,
                'STARTCONF_INIT'      => ConferenceStartInit::class,
                'STARTCONF_FAIL'      => ConferenceStartFail::class,
            ];

            $confState = $tempData['conf_state'] ?? 'NONE';
            $eventClass = $stateMapping[$confState] ?? ConferenceCallEvent::class;

            broadcast(new $eventClass($tempData, $ids['parent_id'], $domain));
        }

        return NeoxTempCallLog::create($tempData);
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
}
