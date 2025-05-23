<?php

namespace App\Repositories;

use App\Events\ReceivedMessages;
use App\Http\Response\CustomApiResponse;
use App\Models\Users;
use App\Models\Message;
use App\Models\MessageAttachment;
use Carbon\Carbon;
use Exception;

class AgentMessageRepository
{
    private $apiResponse;

    public function __construct(
        CustomApiResponse $customApiResponse,
    ) {
        $this->apiResponse = $customApiResponse;
    }

    public function getStatus($request)
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));

            $agentsQuery = Users::with('liveAgent')
                ->select('users.*')
                ->addSelect([
                    'latest_message_time' => Message::select('created_at')
                        ->where('type', 'agent_chat')
                        ->where(function ($query) {
                            // $query->whereColumn('receiver_id', 'users.id')
                            //     ->orWhereColumn('user_id', 'users.id');
                            $query->where('receiver_id', auth()->user()->id)
                                ->orWhere('user_id', auth()->user()->id);
                        })
                        ->orderByDesc('created_at')
                        ->limit(1)
                ])
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhereExists(function ($subquery) use ($search) {
                                $subquery->select('id')
                                    ->from('messages')
                                    ->where('type', 'agent_chat')
                                    ->whereRaw('messages.user_id = users.id OR messages.receiver_id = users.id')
                                    ->where('messages.text', 'like', "%$search%");
                            });
                    });
                })
                ->where('id', '<>', auth()->user()->id)
                ->orderByDesc('latest_message_time');

            $paginatedAgents = $agentsQuery->paginate($perPage);
            $transformedCollection = $paginatedAgents->getCollection()->transform(function ($user) {
                $lastMessage = $user->latest_message;
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'online_status' => $user->online_status,
                    'last_message' => $lastMessage->text ?? null,
                    'last_message_time' => $lastMessage && $lastMessage->created_at
                        ? Carbon::parse($lastMessage->created_at)->format('Y-m-d H:i:s') ?? null
                        : null,
                    'unread_count' => $user->unread_message ?? 0,
                ];
            });

            $sortedCollection = $transformedCollection->sortByDesc(function ($item) {
                return $item['last_message_time'] ?? '';
            })->values();
            $paginatedAgents->setCollection($sortedCollection);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $paginatedAgents, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating User Status: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getMessages($request, $agentId)
    {
        try {
            $search = $request->input('search', '');

            $currentUserId = auth()->user()->id;

            $messages = Message::where('type', 'agent_chat')
                ->betweenUsers($currentUserId, $agentId)
                ->orderBy('created_at', 'desc');

            if ($search) {
                $messages->where('text', 'LIKE', '%' . $search . '%');
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));

            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $chatMessages = $page == config('omni.ALL_PAGE') ? $messages->get() : $messages->paginate($perPage);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $chatMessages, trans('message.data_fetch'));
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
                "user_id" => auth()->user()->id,
                "receiver_id" => $request->agent_id,
                "type" => 'agent_chat',
                "text" => $request->text,
                "is_read" => 'unread',
                "attachment" => $file['path'] ?? null,
                "sender" => 'agent',
                'external_message_time' => Carbon::now(),
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
                $message->load('attachments');
                broadcast(new ReceivedMessages($message));
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function lastReadMessage($request)
    {
        try {
            Message::where('is_read', 'unread')
                ->where('user_id', $request->agent_id)
                ->where('id', '<=', $request->last_message_id)
                ->update(['is_read' => 'read']);

            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.message_read'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
