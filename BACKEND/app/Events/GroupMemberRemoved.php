<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GroupMemberRemoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $groupId;
    private $userId;
    private $domain;

    public function __construct($groupId, $userId, $domain)
    {
        $this->groupId = $groupId;
        $this->userId = $userId;

        // $this->domain = convertToLower(
        //     Str::replace('.', '_', request()->getHost())
        // );
        $this->domain  = convertToLower(Str::replace('.', '_', $domain));

        generateLogs(
            'info',
            'GroupMemberRemoved Event Fired' .
                json_encode([
                    'group_id' => $groupId,
                    'user_id' => $userId
                ])
        );
    }

    public function broadcastOn()
    {
        $channelName = "{$this->domain}-group_{$this->groupId}-chat";

        generateLogs(
            'info',
            'GroupMemberRemoved Channel' .
                json_encode(['channel' => $channelName])
        );

        return new PrivateChannel($channelName);
    }

    public function broadcastWith()
    {
        generateLogs(
            'info',
            'GroupMemberRemoved Payload' .
                json_encode(['user_id' => $this->userId])
        );
        return [
            'type' => 'group_member_removed',
            'group_id' => $this->groupId,
            'user_id' => $this->userId,
        ];
    }
}
