<?php

namespace App\Repositories;

use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Services\Chat360Service;
use App\Events\CustomerChatRequest;
use App\Events\ChatMessagesStored;
use Carbon\Carbon;
use App\Models\MessageAttachment;

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
            $agentAssigned = Message::where('room_id', $request->room_id)
                ->where('user_id', auth()->user()->id)->first();

            if ($agentAssigned) {
                $boradCastData = [
                    'room_id' => $request->room_id,
                    'user_id' => auth()->user()->id
                ];
                broadcast(new CustomerChatRequest($boradCastData, auth()->user()->domain));
            } else {
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

                        if (is_array($messageText)) {
                            $messageText = implode(', ', $messageText);
                        }

                        if (is_string($messageText)) {
                            $messageText = trim($messageText, "[]\"");
                        }

                        $timestamp = isset($msg['timestamp_int'])
                            ? Carbon::createFromTimestamp((float) $msg['timestamp_int'])
                            : Carbon::now();

                        $chatMessage[] = [
                            'user_id' => auth()->user()->id,
                            'type' => 'user_chat',
                            'text' => strip_tags($messageText),
                            'room_id' => $request->room_id,
                            'sender' => $sender,
                            'external_user_id' => $chatHistory['admin_id'] ?? null,
                            'is_read' => 'read',
                            'external_message_time' => $timestamp,
                            'node_type' => $msg['nodeType'] ?? $msg['data']['nodeType'] ?? 'MESSAGE',
                            'session_id' => $msg['session_id'] ?? null,
                            'first_name' => $msg['first_name'] ?? null,
                            'last_name' => $msg['last_name'] ?? nulL,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                    if (!empty($chatMessage)) {
                        // broadcast(new ReceivedCustomerMessages($chatMessage));
                        Message::insert($chatMessage);
                        generateLogs('info', "Inserted " . count($chatMessage) . " chat messages successfully.");
                    } else {
                        generateLogs('error', "No Valid message to insert.");
                    }
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
            $messages  = Message::where("type", "user_chat")
                ->where('user_id', auth()->user()->id)->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage($request)
    {
        try {
            $files = uploadAttachment($request, "attachment", "attachment");

            $message = Message::Create([
                'user_id' => auth()->user()->id,
                'text' => $request->text,
                'is_read' => 'read',
                'type' => 'user_chat',
                'sender' => 'agent',
                'external_message_time' => Carbon::now()
            ]);

            if ($message) {
                if ($files) {
                    foreach ($files as $file) {
                        $fileData[] = [
                            'message_id' => $message->id,
                            'attachments' => $file['fileName'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    MessageAttachment::insert($fileData);
                }
                $this->chat360Service->sendMessage('text', ['body' => $request->text]);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function fetchChatResponse($request, $details)
    {
        try {
            $wabResponse = $request->input('wab_response');
            $messageBody = $wabResponse['messages'][0]['text']['body'] ?? null;
            $messageType = $wabResponse['messages'][0]['type'] ?? null;

            $existingMessage = Message::where('external_user_id', $request->input('user_id'))->first();

            $message = Message::Create([
                'user_id' => $existingMessage->user_id,
                'type' => 'user_chat',
                'text' => strip_tags($messageBody),
                'sender' => 'end_user',
                'is_read' => 'read',
                'external_user_id' => $request->input('user_id'),
                'external_message_time' => $request->input('timestamp') ?? Carbon::now(),
                'node_type' => $messageType ?? 'MESSAGE',
                'first_name' => $request->input('receiver_name') ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if($message) {
                broadcast(new ChatMessagesStored($message, $details->domain, $existingMessage->user_id));
            }

            generateLogs('info', 'Stored data for UserChat: ' . json_encode($message));
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Chat360 Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
