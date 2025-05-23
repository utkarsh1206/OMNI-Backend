<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Services\Chat360Service;
use App\Events\ReceivedCustomerMessages;

class UserMessageRepository
{
    private $apiResponse;

    private $chat360Service;

    public function __construct(
        CustomApiResponse $customApiResponse,
        Chat360Service $chat360Service
    ) {
        $this->apiResponse = $customApiResponse;
        $this->chat360Service = $chat360Service;
    }

    public function assignChatToAgent($request)
    {
        try {
            $chatHistory = $this->chat360Service->getChatHistory($request->room_id);

            if ($chatHistory) {
                if (! isset($chatHistory['messages'])) {
                    generateLogs('error', 'No messages found in API response.');
                }

                foreach ($chatHistory['messages'] as $msg) {
                    $sender = $msg['user'] ?? null;

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

                    $timestamp = isset($msg['timestamp_int'])
                        ? Carbon::createFromTimestamp((float) $msg['timestamp_int'])
                        : now();

                    $chatMessage[] = [
                        //'id' => $msg['currentId'] ?? Str::uuid()->toString(),
                        'user_id' => auth()->user()->id,
                        'type' => 'user_chat',
                        'attachment' => null,
                        'text' => strip_tags($messageText),
                        'room_id' => $request->room_id,
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
                    broadcast(new ReceivedCustomerMessages($chatMessage));
                    Message::insert($chatMessage);
                    generateLogs('info', "Inserted " . count($chatMessage) . " chat messages successfully.");
                } else {
                    generateLogs('error', "No Valid message to insert.");
                }
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getMessages()
    {
        try {
            $userId = auth()->user()->id;
            $messages  = Message::where("type", "user_chat")->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage($request)
    {
        try {
            $file = uploadAttachment($request, "attachment", "attachment");

            $message = Message::Create([
                "user_id" => auth()->user()->id,
                "text" => $request->text,
                "is_read" => 'unread',
                "type" => 'user_chat',
                "attachment" => $file['path'] ?? null,
                "sender" => 'agent',
            ]);

            if ($message) {
                $this->chat360Service->sendMessage('text', ['body' => $request->text]);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
