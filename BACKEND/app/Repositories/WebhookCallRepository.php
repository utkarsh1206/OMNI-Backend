<?php

namespace App\Repositories;

use App\Events\CallConnected;
use App\Events\CallHangup;
use App\Events\CallInitiated;
use App\Models\NeoxCallLog;
use App\Models\NeoxTempCallLog;
use App\Events\DialCenterData;

class WebhookCallRepository
{
    // Save to neox_call_logs table
    public function saveCallLog($data)
    {
        return NeoxCallLog::create($data);
    }

    // Save to neox_temp_call_logs table
    public function saveTempCallLog($tempData, $id, $domain)
    {
        generateLogs('info', 'tempdata: ' . json_encode($tempData));

        $broadcastEvents = [
            'call_initiated' => CallInitiated::class,
            'call_connected' => CallConnected::class,
            'call_hangup'    => CallHangup::class,
        ];

        if (isset($broadcastEvents[$tempData['type']])) {
            broadcast(new $broadcastEvents[$tempData['type']]($tempData, $id, $domain));
        }

        // broadcast(new DialCenterData($tempData));
        return NeoxTempCallLog::create($tempData);
    }

    // Update or create call history
    public function updateOrCreateCallHistory($data)
    {
        return NeoxCallLog::updateOrCreate(
            [
                'call_unique_id' => $data['call_unique_id'],
                'call_date' => $data['call_date'],
                'called_phone_number' => $data['called_phone_number'],
            ],
            $data
        );
    }
}
