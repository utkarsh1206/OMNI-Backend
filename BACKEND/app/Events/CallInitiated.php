<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData;
    private $dialerId;
    private $dialerDomain;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $id, $domain)
    {
        $this->dialerData = $data;
        $this->dialerId = $id;
        $this->dialerDomain = $domain;
        generateLogs('info', 'broadcast Data: ' . json_encode(['dialerData' => $this->dialerData]));
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
        generateLogs('info', 'broadcastWith Data: ' . json_encode(['dialerData' => $this->dialerData]));
        return [
            'dialCenter' => $this->dialerData,
        ];
    }
}
