<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Chat360Service;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Message;

class fetchChat360Message extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-chat360-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is use fetch chat360 chat & store them into database';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chat360Services = new Chat360Service();
        $chatHistory = $chat360Services->getChatHistory();

        if ($chatHistory) {
            if (! isset($chatHistory['messages'])) {
                generateLogs('error', 'No messages found in API response.');
            }

            foreach ($chatHistory['messages'] as $msg) {
                $sender = $msg['user'] ?? null;

                // Only process bot and user messages
                if (! in_array($sender, ['bot', 'end_user'])) {
                    continue;
                }

                // Determine message text
                if ($sender === 'bot') {
                    $messageText = $msg['data']['questionText']
                        ?? ($msg['data']['messages'][0] ?? null);
                } else {
                    $messageText = $msg['post_data'] ?? $msg['message'] ?? null;
                }

                if (empty($messageText)) {
                    continue;
                }

                // Handle timestamp
                $timestamp = isset($msg['timestamp_int'])
                    ? Carbon::createFromTimestamp((float) $msg['timestamp_int'])
                    : now();

                // Store message
                $chatMessage = [
                    'id' => $msg['currentId'] ?? Str::uuid()->toString(),
                    'room_id' => $msg['room_id'] ?? $chatHistory['room_id'],
                    'sender' => $sender,
                    'message' => strip_tags($messageText),
                    'timestamp' => $timestamp,
                    'node_type' => $msg['nodeType'] ?? $msg['data']['nodeType'] ?? 'MESSAGE',
                    'session_id' => $msg['session_id'] ?? null,
                    'first_name' => $msg['first_name'] ?? null,
                    'last_name' => $msg['last_name'] ?? nulL,
                ];
                echo '<pre>';
                print_r($chatMessage);
            }
            //    Message::insert($chatMessage);
            generateLogs('info', 'Chat messages from bot and user stored successfully.');
            die;
        }
    }
}
