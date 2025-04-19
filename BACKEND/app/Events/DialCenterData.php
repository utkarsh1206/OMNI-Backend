<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DialCenterData implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->dialerData = $data;
        generateLogs('info', 'broadcast Data:'.['dialerData' => $this->dialerData]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs('info', 'Channel Name:'.['Channel ' => "dialer_.{$this->dialerData->type}"]);
        return [
            new Channel("dialer_.{$this->dialerData->type}"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data:'.['dialCenter' => $this->dialerData]);
        return [
            'dialCenter' => $this->dialerData, // Make sure this is returning what you need
        ];
    }
}
