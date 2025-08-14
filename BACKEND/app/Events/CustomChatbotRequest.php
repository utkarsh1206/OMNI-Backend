<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomChatbotRequest implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $customerChat, $domain;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->customerChat = $data;
        // $this->domain = $domain;
        generateLogs('info', 'broadcast Data: '.json_encode(['customChatbotRequest' => $this->customerChat]));
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
                'Channel' => "custom-chatbot-request"
            ])
        );
        return [
            new PrivateChannel("custom-chatbot-request"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['customChatbotRequest' => $this->customerChat]));
        return [
            'customChatbotRequest' => $this->customerChat, 
        ];
    }
}
