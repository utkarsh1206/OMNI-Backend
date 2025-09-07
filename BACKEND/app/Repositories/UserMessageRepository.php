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
                    'user_id' => auth()->user()->id,
                    'external_user_phone'=> $agentAssigned->external_user_phone,
                ];
                broadcast(new CustomerChatRequest($boradCastData, auth()->user()->domain));
            } else {
                $chatHistory = $this->chat360Service->getChatHistory($request->room_id);
                if ($chatHistory) {
                    if (! isset($chatHistory['messages'])) {
                        generateLogs('error', 'No messages found in API response.');
                    }
                    $phoneNumber = $chatHistory['variables']['@user_number'] ?? $chatHistory['variables']['@phone'] ?? null;
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
                            'sender' => $sender,
                            'room_id' => $request->room_id,
                            'session_id' => $msg['session_id'] ?? null,
                            'external_user_id' => $chatHistory['admin_id'] ?? null,
                            'node_type' => $msg['nodeType'] ?? $msg['data']['nodeType'] ?? 'MESSAGE',
                            'text' => strip_tags($messageText),    
                            'first_name' => $msg['first_name'] ?? null,
                            'last_name' => $msg['last_name'] ?? nulL,
                            'external_user_phone' => $phoneNumber,
                            'is_read' => 'read',
                            'external_message_time' => $timestamp,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                    if (!empty($chatMessage)) {
                        $boradCastData = [
                            'room_id' => $request->room_id,
                            'user_id' => auth()->user()->id,
                            'external_user_phone'=> $phoneNumber,
                        ];
                        broadcast(new CustomerChatRequest($boradCastData, auth()->user()->domain));
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

    public function getUserPhones()
    {
        try {
            // $messages  = Message::select('external_user_phone')->where("type", "user_chat")
            //     ->where('user_id', auth()->user()->id)
            //     ->whereNotNull('external_user_phone')
            //     ->distinct()
            //     ->orderBy('external_user_phone', 'desc')
            //     ->pluck('external_user_phone');

            $messages = Message::where("type", "user_chat")
                ->where("user_id", auth()->id())
                ->whereNotNull("external_user_phone")
                ->where("external_user_phone", '!=', '')
                ->orderBy("id", "desc") // newest first
                ->get()
                ->groupBy('external_user_phone')
                ->map(function ($group) {
                    // Pick the first non-system valid name
                    $name = $group->map(fn($msg) => trim(($msg->first_name ?? '') . ' ' . ($msg->last_name ?? '')))
                        ->first(fn($n) => !empty($n) && strtolower($n) !== 'system message');

                    return [
                        'name'  => $name ?: 'System Message',
                        'phone' => $group->first()->external_user_phone,
                    ];
                })
                ->values();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getMessages($request)
    {
        try {
            $messages  = Message::where("type", "user_chat")
                ->where('user_id', auth()->user()->id)
                ->where('external_user_phone', $request->phone)
                ->get();

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
                $this->chat360Service->sendMessage('text', ['body' => $request->text], $request->phone_number);
                // $msg =  Message::where('user_id', auth()->user()->id)
                //         ->where('type', 'user_chat')->first();
                // if($msg) {
                //     $this->chat360Service->sendMessage('text', ['body' => $request->text], $msg->external_user_phone);
                // }
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

            if (!$existingMessage) {
                generateLogs('info', "First time message received from external user id");
                return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.message_created'));    
            }

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
                'external_user_phone' => $request->input('receiver_num') ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if($message) {
                broadcast(new ChatMessagesStored($message, $details->domain, $existingMessage->user_id));
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Chat360 Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
