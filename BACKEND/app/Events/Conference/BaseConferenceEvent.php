<?php

namespace App\Events\Conference;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseConferenceEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dialerData;
    protected $dialerId;
    protected $dialerDomain;

    public function __construct($data, $id, $domain)
    {
        $this->dialerData = $data;
        $this->dialerId = $id;
        $this->dialerDomain = $domain;

        $eventName = class_basename($this); 
        generateLogs('info', "Broadcasting Conference Event: " . $eventName . "\nData : " . json_encode(['dialerData' => $this->dialerData]));
    }

    public function broadcastOn(): array
    {
        generateLogs(
            'info',
            'Channel Name: ' . json_encode([
                'Channel' => "{$this->dialerDomain}-{$this->dialerId}-conference-events"
            ])
        );
        return [
            new PrivateChannel("{$this->dialerDomain}-{$this->dialerId}-conference-events"),
        ];
    }

    public function broadcastWith(): array
    {
        generateLogs('info', 'BaseConferenceEvent BroadcastWith Data: ' . json_encode($this->dialerData));
        return $this->dialerData;
    }
}