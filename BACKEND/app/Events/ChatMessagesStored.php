<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ChatMessagesStored implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $message, $domain, $id;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $domain, $id)
    {
        $this->message = $data;
        $this->domain  = convertToLower(Str::replace('.', '_', $domain));
        $this->id = $id;
        generateLogs('info', 'ChatMessages Stored Broadcast Data: '.json_encode(['customerChat' => $this->message]));
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
                'Channel' => "{$this->domain}-{$this->id}-customer-chat"
            ])
        );
        return [
            new PrivateChannel("{$this->domain}-{$this->id}-customer-chat"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'Chat Messages Stored BroadcastWith Data: '.json_encode(['chatMessagesStored' => $this->message]));
        return [
            'chatMessagesStored' => $this->message, 
        ];
    }
}
