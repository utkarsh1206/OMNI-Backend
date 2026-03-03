<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConferenceCallEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $dialerData, $dialerId, $dialerDomain;

    public function __construct($data, $id, $domain)
    {
        $this->dialerData = $data;
        $this->dialerDomain = $domain;
        $this->dialerId = $id;

        generateLogs('info', 'Conference Broadcast Data: ' . json_encode(['dialerData' => $this->dialerData]));
    }

    public function broadcastOn(): array
    {
        $channelName = "{$this->dialerDomain}-{$this->dialerId}-conference-events";

        generateLogs('info', 'Conference Channel Name: ' . $channelName);

        return [
            new PrivateChannel($channelName),
        ];
    }

    public function broadcastWith()
    {
        // return [
        //     'dialCenter' => $this->dialerData,
        // ];

        generateLogs('info', 'Conference BroadcastWith Data: ' . json_encode($this->dialerData));

        return $this->dialerData;
    }
}
