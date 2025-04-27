<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData;

    private $dialerDomain;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $domain)
    {
        $this->dialerData = $data;
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
                'Channel' => "{$this->dialerDomain}-call-events"
            ])
        );
        return [
            new Channel("{$this->dialerDomain}-call-events"),
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
