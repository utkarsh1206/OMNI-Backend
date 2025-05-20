<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessagesStored implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $messageHistory;

    private $domain;

    private $id;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->messageHistory = $data;
        $this->domain = auth()->user()->domain;
        $this->id = auth()->user()->id;
        generateLogs('info', 'broadcast Data: '.json_encode(['chatMessagesStored' => $this->messageHistory]));
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
                'Channel' => "{$this->domain}-{$this->id}-external-messages"
            ])
        );
        return [
            new Channel("{$this->domain}-{$this->id}-external-messages"),
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
