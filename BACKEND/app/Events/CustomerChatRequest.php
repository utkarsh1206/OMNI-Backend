<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerChatRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $roomId;

    private $domain;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->roomId = $data['room_id'];
        $this->domain = $data['domain'];
        generateLogs('info', 'broadcast Data: '.json_encode(['roomId' => $this->roomId]));
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
                'Channel' => "{$this->domain}-customer-chat-request"
            ])
        );
        return [
            new Channel("{$this->domain}-customer-chat-request"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['roomId' => $this->roomId]));
        return [
            'roomId' => $this->roomId, 
        ];
    }
}
