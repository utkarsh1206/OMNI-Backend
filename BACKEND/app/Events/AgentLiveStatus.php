<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentLiveStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $agentLiveData, $domain, $authId;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->agentLiveData = $data;
        $this->domain = auth()->user()->domain;
        $this->authId = auth()->user()->id;
        generateLogs('info', 'broadcast Data: ' . json_encode(['AgentLiveStatus' => $this->agentLiveData]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        generateLogs('info', 'Channel Name: ' . json_encode(['Channel' =>"{$this->domain}-{$this->authId}-agent-status"]));
        return [
            new Channel("{$this->domain}-{$this->authId}-agent-status"),
        ];
    }

    public function broadcastWith()
    {
        generateLogs('info', 'broadcastWith Data: ' . json_encode(['AgentLiveStatus' => $this->agentLiveData]));
        return [
            'AgentLiveStatus' => $this->agentLiveData,
        ];
    }
}
