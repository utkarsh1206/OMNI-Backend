<?php

namespace App\Repositories;

use App\Models\NeoxCallLog;
use App\Models\NeoxTempCallLog;

class WebhookCallRepository
{
    // Save to neox_call_logs table
    public function saveCallLog($data)
    {
        return NeoxCallLog::create($data);
    }

    // Save to neox_temp_call_logs table
    public function saveTempCallLog($tempData)
    {
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
