<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallConnected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData, $dialerId, $dialerDomain;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $id, $domain)
    {
        $this->dialerData = $data;
        $this->dialerDomain = $domain;
        $this->dialerId = $id;
        generateLogs('info', 'Call Connected Broadcast Data: ' . json_encode(['dialerData' => $this->dialerData]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs(
            'info',
            'Channel Name: ' . json_encode([
                'Channel' => "{$this->dialerDomain}-{$this->dialerId}-call-events"
            ])
        );
        return [
            new PrivateChannel("{$this->dialerDomain}-{$this->dialerId}-call-events"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'Call Connected BroadcastWith Data: ' . json_encode(['dialerData' => $this->dialerData]));
        return [
            'dialCenter' => $this->dialerData,
        ];
    }
}
