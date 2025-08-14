<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NotifyCustomer implements ShouldBroadcastNow
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
        generateLogs('info', 'broadcast Data: '.json_encode(['notifyCustomer' => $this->customerChat]));
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
                'Channel' => "notify-customer"
            ])
        );
        return [
            new PrivateChannel("notify-customer"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: '.json_encode(['notifyCustomer' => $this->customerChat]));
        return [
            'notifyCustomer' => $this->customerChat, 
        ];
    }
}
