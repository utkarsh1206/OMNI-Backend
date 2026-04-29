<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GroupMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $groupId;
    public $messageId;
    private $domain;

    public function __construct($groupId, $messageId, $domain)
    {
        $this->groupId = $groupId;
        $this->messageId = $messageId;

        $this->domain = convertToLower(
            Str::replace('.', '_', $domain)
        );

        generateLogs(
            'info',
            'GroupMessageDeleted Event Fired',
            json_encode([
                'group_id' => $groupId,
                'message_id' => $messageId
            ])
        );
    }

    public function broadcastOn()
    {
        $channelName = "{$this->domain}-group_{$this->groupId}-chat";

        generateLogs(
            'info',
            'GroupMessageDeleted Channel ' . json_encode(['channel' => $channelName])
        );

        return new PrivateChannel($channelName);
    }

    public function broadcastWith()
    {
        generateLogs(
            'info',
            'GroupMessageDeleted Payload' .
                json_encode(['message_id' => $this->messageId])
        );
        return [
            'type' => 'group_message_deleted',
            'group_id' => (int) $this->groupId,
            'message_id' => (int) $this->messageId,
        ];
    }
}