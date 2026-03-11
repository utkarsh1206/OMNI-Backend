<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CustomerChatRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $customerChat, $domain;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $domain)
    {
        $this->customerChat = $data;
        $this->domain  = convertToLower(Str::replace('.', '_', $domain));
        generateLogs('info', 'Customer Chat Request Broadcast Data: '.json_encode(['customerChat' => $this->customerChat]));
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
            new PrivateChannel("{$this->domain}-customer-chat-request"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'Custom Chat Request BroadcastWith Data: '.json_encode(['customerChat' => $this->customerChat]));
        return [
            'customerChat' => $this->customerChat, 
        ];
    }
}
