<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DialCenterData implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->dialerData = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            new PrivateChannel("dialer_.{$this->dialerData->type}"),
        ];
    }

    public function broadcastWith()
    {
        return [
            'dialCenter' => $this->dialerData, // Make sure this is returning what you need
        ];
    }
}
