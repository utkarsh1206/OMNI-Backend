<?php

namespace App\Repositories;

use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Models\Users;
use App\Events\ReceiveMessages;
use Carbon\Carbon;

class AgentMessageRepository
{
    private $apiResponse;

    public function __construct(
        CustomApiResponse $customApiResponse,
    ) {
        $this->apiResponse = $customApiResponse;
    }

    public function getStatus()
    {
        try {
            $agents = Users::with('liveAgent')
                ->where('type', 3)
                ->get(['id', 'first_name', 'last_name', 'email'])
                ->map(function ($user) {
                    $lastMessage = Message::where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->orWhere('receiver_id', $user->id);
                    })
                        ->orderByDesc('created_at')
                        ->first();

                    return [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'online_status' => $user->online_status,
                        'last_message' => $lastMessage->text ?? null,
                        'last_message_time' => $lastMessage && $lastMessage->created_at
                            ? Carbon::parse($lastMessage->created_at)->format('Y-m-d H:i:s')
                            : null,
                    ];
                });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $agents, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating User Status: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getMessages($agentId)
    {
        try {
            $currentUserId = auth()->user()->id;

            $messages = Message::where('type', 'agent_chat')
                ->where(function ($query) use ($currentUserId, $agentId) {
                    $query->where(function ($q) use ($currentUserId, $agentId) {
                        $q->where('user_id', $currentUserId)
                            ->where('receiver_id', $agentId);
                    })->orWhere(function ($q) use ($currentUserId, $agentId) {
                        $q->where('user_id', $agentId)
                            ->where('receiver_id', $currentUserId);
                    });
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage($request)
    {
        // File upload logic is remaining here. We need to make file upload logic in common place
        try {
            $file = uploadAttachment($request, "attachment", "attachment");
            $message = Message::Create([
                "user_id" => auth()->user()->id,
                "receiver_id" => $request->agent_id,
                "type" => 'agent_chat',
                "text" => $request->text,
                "is_read" => 'unread',
                "attachment" => $file['path'] ?? null,
                "sender" => 'agent',
            ]);

            if ($message) {
                broadcast(new ReceiveMessages($message));
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
