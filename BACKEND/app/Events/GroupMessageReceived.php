<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GroupMessageReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    // private $receiverId;
    private $domain;

    /**
     * Create a new event instance.
     */
    // public function __construct($message, $receiverId, $domain)
    public function __construct($message, $domain)
    {
        $this->message = $message;
        // $this->receiverId = $receiverId;

        // $this->domain = convertToLower(
        //     Str::replace('.', '_', request()->getHost())
        // );
        $this->domain  = convertToLower(Str::replace('.', '_', $domain));

        generateLogs(
            'info',
            'GroupMessageReceived Event Fired',
            json_encode([
                'group_id' => $message->group_id,
                // 'receiver_id' => $receiverId,
                'message_id' => $message->id
            ])
        );
    }

    /**
     * Broadcast channel
     */
    public function broadcastOn()
    {
        $channelName = "{$this->domain}-group_{$this->message->group_id}-chat";

        generateLogs(
            'info',
            'GroupMessageReceived Channel' .
                json_encode(['channel' => $channelName])
        );

        return new PrivateChannel($channelName);
    }

    /**
     * Broadcast payload
     */
    public function broadcastWith()
    {
        generateLogs(
            'info',
            'GroupMessageReceived Payload' .
                json_encode(['message' => $this->message])
        );

        return [
            'type' => 'group_message',
            'group_id' => $this->message->group_id,
            // 'receiver_id' => $this->receiverId,
            'message' => $this->message,
        ];
    }
}
