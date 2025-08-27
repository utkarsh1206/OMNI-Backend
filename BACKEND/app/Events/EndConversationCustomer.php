<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class EndConversationCustomer implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $customerId, $domain, $message;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $domain, $customerId)
    {
        $this->customerId = $customerId;
        $this->domain  = convertToLower(Str::replace('.', '_', $domain));
        $this->message = $data;
        generateLogs('info', 'broadcast Data: '.json_encode(['EndConversationCustomer' => $this->message]));
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
                'Channel' => "{$this->domain}-customer_{$this->customerId}-custom-chatbot"
            ])
        );
        return [
            new PrivateChannel("{$this->domain}-customer_{$this->customerId}-custom-chatbot"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['notifyCustomer' => $this->message]));
        return [
            'EndConversationCustomer' => $this->message, 
        ];
    }
}
