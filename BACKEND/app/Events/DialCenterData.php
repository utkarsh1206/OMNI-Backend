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
        generateLogs('info', 'broadcast Data: '.json_encode(['dialerData' => $this->dialerData]));
    }

    // /**
    //  * The event's broadcast name.
    //  */
    // public function broadcastAs(): string
    // {
    //     return 'DialCenterData';
    // }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs('info', 'Channel Name: '.json_encode(['Channel' => "dialer_{$this->dialerData['type']}"]));
        return [
            new Channel("dialer_{$this->dialerData['type']}"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['dialerData' => $this->dialerData]));
        return [
            'dialCenter' => $this->dialerData, 
        ];
    }
}
