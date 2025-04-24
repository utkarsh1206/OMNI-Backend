<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessagesStored implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $messageHistory;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->messageHistory = $data;
        generateLogs('info', 'broadcast Data: '.json_encode(['chatMessagesStored' => $this->messageHistory]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs('info', 'Channel Name: '.json_encode(['Channel' => "fetch_message_history"]));
        return [
            new PrivateChannel("fetch_message_history"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['chatMessagesStored' => $this->messageHistory]));
        return [
            'chatMessagesStored' => $this->messageHistory, 
        ];
    }
}
