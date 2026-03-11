<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;


class AgentForceLogout implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $domain;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, $domain = null)
    {
        $this->userId = $userId;
        $this->domain = convertToLower(Str::replace('.', '_', $domain ?? auth()->user()->domain));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        generateLogs('info', 'Channel Name: ' . json_encode(['Channel' => "{$this->domain}-{$this->userId}-force-logout"]));
        return new PrivateChannel("{$this->domain}-{$this->userId}-force-logout");
    }

    public function broadcastWith()
    {
        generateLogs('info', 'Agent Force Logout BroadcastWith Data: ' . json_encode(
            [
                'AgentForceLogout' => $this->userId,
                'Domain' => $this->domain
            ]
        ));
        return [
            'message' => 'You have been logged out by an administrator.',
            'user_id' => $this->userId,
        ];
    }
}
