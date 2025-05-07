<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Services\Chat360Service;
use Carbon\Carbon;
use App\Models\Message;
use App\Events\ChatMessagesStored;

class ChatbotWebhookRepository
{
    private $apiResponse;

    private $chat360Service;

    public function __construct(CustomApiResponse $customApiResponse, 
    Chat360Service $chat360Service)
    {
        $this->apiResponse = $customApiResponse;
        $this->chat360Service = $chat360Service;
    }

    public function fetchMessageFromRoom(Request $request)
    {
        try {                
            $chatHistory = $this->chat360Service->getChatHistory();

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
                    $chatMessage[] = [
                        //'id' => $msg['currentId'] ?? Str::uuid()->toString(),
                        'user_id' => null,
                        'type' => 'user_chat',
                        'attachment' => null,
                        'text' => strip_tags($messageText),
                        'room_id' => $msg['room_id'] ?? $chatHistory['room_id'],
                        'sender' => $sender,
                        'external_message_time' => $timestamp,
                        'node_type' => $msg['nodeType'] ?? $msg['data']['nodeType'] ?? 'MESSAGE',
                        'session_id' => $msg['session_id'] ?? null,
                        'first_name' => $msg['first_name'] ?? null,
                        'last_name' => $msg['last_name'] ?? nulL,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
    
                if (!empty($chatMessage)) {
                    broadcast(new ChatMessagesStored($chatMessage));
                    Message::insert($chatMessage);
                    generateLogs('info', "Inserted " . count($chatMessage) . " chat messages successfully.");
                } else {
                    generateLogs('error', "No Valid message to insert.");
                }
            }
            
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Storing Messages of Room: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

}
