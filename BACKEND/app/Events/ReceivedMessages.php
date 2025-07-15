<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ReceivedMessages implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $message, $receiverId, $domain;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->message = $data;
        $this->domain = convertToLower(Str::replace('.', '_', auth()->user()->domain));
        $this->receiverId = $data->receiver_id;
        generateLogs('info', 'broadcast Data: ' . json_encode(['AgentChatMessages' => $this->message]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs('info', 'Channel Name: ' . json_encode(['Channel' => "{$this->domain}-{$this->receiverId}-agent-chat"]));
        return [
            new PrivateChannel("{$this->domain}-{$this->receiverId}-agent-chat"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: ' . json_encode(['AgentChatMessages' => $this->message]));
        return [
            'AgentChatMessages' => $this->message,
        ];
    }
}
