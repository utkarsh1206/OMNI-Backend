<?php

namespace App\Repositories;

use App\Events\GroupMessageReceived;
use App\Events\ReceivedMessages;
use App\Http\Response\CustomApiResponse;
use App\Models\Group;
use App\Models\Message;
use App\Models\GroupParticipant;
use App\Models\MessageAttachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;

class UnifiedChatRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getChats($request)
    {
        try {
            $userId = auth()->id();
            $perPage = $request->input('per_page', config('omni.PER_PAGE', 15));
            $search = $request->get('search');

            $individualQuery = DB::table('users')
                ->leftJoin('live_agents', 'users.id', '=', 'live_agents.user_id')
                ->select([
                    'users.id',
                    DB::raw("'individual' as chat_type"),
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                    DB::raw("CASE WHEN live_agents.id IS NOT NULL THEN 'online' ELSE 'offline' END as online_status"),
                    // Subquery for last message time
                    DB::raw("(SELECT created_at FROM messages 
                          WHERE (receiver_id = users.id AND user_id = {$userId}) 
                          OR (user_id = users.id AND receiver_id = {$userId}) 
                          ORDER BY created_at DESC LIMIT 1) as last_message_time"),
                    // Subquery for last message text
                    DB::raw("(SELECT text FROM messages 
                          WHERE (receiver_id = users.id AND user_id = {$userId}) 
                          OR (user_id = users.id AND receiver_id = {$userId}) 
                          ORDER BY created_at DESC LIMIT 1) as last_message_text"),
                    // Unread count
                    DB::raw("(SELECT COUNT(*) FROM messages 
                          WHERE user_id = users.id AND receiver_id = {$userId} 
                          AND is_read = 'unread') as unread_count")
                ])
                ->where('users.id', '<>', $userId)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('users.first_name', 'like', "%{$search}%")
                            ->orWhere('users.last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$search}%"]);
                    });
                });

            $groupQuery = DB::table('groups')
                ->join('group_participants', 'groups.id', '=', 'group_participants.group_id')
                ->select([
                    'groups.id',
                    DB::raw("'group' as chat_type"),
                    'groups.name',
                    DB::raw("NULL as online_status"),
                    // Last message time
                    DB::raw("(SELECT created_at FROM messages 
                          WHERE messages.group_id = groups.id 
                          ORDER BY created_at DESC LIMIT 1) as last_message_time"),
                    // Last message text
                    DB::raw("(SELECT text FROM messages 
                          WHERE messages.group_id = groups.id 
                          ORDER BY created_at DESC LIMIT 1) as last_message_text"),
                    // Unread count (comparing against last_read_message_id)
                    DB::raw("(SELECT COUNT(*) FROM messages 
                          WHERE messages.group_id = groups.id 
                          AND messages.id > COALESCE(group_participants.last_read_message_id, 0)) as unread_count")
                ])
                ->where('group_participants.user_id', $userId)
                ->whereNull('group_participants.left_at')
                ->when($search, function ($q) use ($search) {
                    $q->where('groups.name', 'like', "%{$search}%");
                });

            $finalQuery = $individualQuery->union($groupQuery);

            $paginatedResults = DB::table(DB::raw("({$finalQuery->toSql()}) as combined"))
                ->mergeBindings($finalQuery)
                ->orderByDesc('last_message_time')
                ->paginate($perPage);

            $paginatedResults->getCollection()->transform(function ($chat) {
                return [
                    'id' => $chat->id,
                    'chat_type' => $chat->chat_type,
                    'name' => $chat->name,
                    'online_status' => $chat->online_status,
                    'last_message' => $chat->last_message_text,
                    'last_message_time' => $chat->last_message_time
                        ? Carbon::parse($chat->last_message_time)->toJSON()
                        : null,
                    'unread_count' => (int) $chat->unread_count,
                ];
            });

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $paginatedResults,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching unified chats', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getMessages($request)
    {
        try {
            $chatType = $request->input('chat_type'); // individual | group
            $chatId   = $request->input('chat_id'); // user_id for individual | group_id for group
            $search   = $request->input('search', '');

            $perPage  = $request->input('per_page', config('omni.PER_PAGE'));
            $page     = $request->input('page', config('omni.DEFAULT_PAGE'));

            $currentUserId = auth()->id();

            if (!$chatType || !$chatId) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'chat_type and chat_id are required',
                    400
                );
            }

            $messages = Message::query()
                ->where('type', 'agent_chat');

            if ($chatType === 'individual') {
                $messages->betweenUsers($currentUserId, $chatId);
            } elseif ($chatType === 'group') {
                $participantCheck = $this->verifyGroupParticipation($chatId, $currentUserId);

                if ($participantCheck instanceof JsonResponse) {
                    return $participantCheck;
                }

                $messages->where('group_id', $chatId);
            } else {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'Invalid chat_type.',
                    400
                );
            }

            if ($search) {
                $messages->where('text', 'like', "%{$search}%");
            }

            $messages->orderBy('created_at', 'desc');

            $data = ($page == config('omni.ALL_PAGE'))
                ? $messages->get()
                : $messages->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching unified messages', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function sendMessage($request)
    {
        try {
            $request->validate([
                'chat_type' => 'required|in:individual,group',
                'chat_id'   => 'required|integer',
                'text'      => 'nullable|string',
            ]);

            $chatType = $request->chat_type;
            $chatId   = $request->chat_id;
            $userId   = auth()->user()->id;

            $receiverId = $chatType === 'individual' ? $chatId : null;
            $groupId    = $chatType === 'group' ? $chatId : null;

            if ($chatType === 'group') {
                $participant = $this->verifyGroupParticipation($chatId, $userId);

                if ($participant instanceof JsonResponse) {
                    return $participant;
                }

                $group = Group::findOrFail($chatId);

                if ($group->type === 'broadcast' && !in_array($participant->role, ['owner', 'admin'])) {
                    return $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        null,
                        'Only owner/admin can send messages in broadcast group',
                        403
                    );
                }
                if ($group->type !== 'broadcast' && !$participant->can_send_message) {
                    return $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        null,
                        'You do not have permission to send messages in this group',
                        403
                    );
                }
            }

            $message = Message::create([
                'user_id'               => $userId,
                'receiver_id'           => $receiverId,
                'group_id'              => $groupId,
                'type'                  => 'agent_chat',
                'text'                  => $request->text,
                'is_read'               => 'unread',
                'sender'                => 'agent',
                'external_message_time' => Carbon::now(),
            ]);

            $files = uploadAttachment($request, "attachment", "attachment");

            if (!empty($files)) {
                $files = isset($files['fileName']) ? [$files] : $files;
                $now = Carbon::now();

                $fileData = [];

                $fileData = array_map(function ($file) use ($message, $now) {
                    return [
                        'message_id'  => $message->id,
                        'attachments' => $file['fileName'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }, $files);

                MessageAttachment::insert($fileData);
            }

            $message->load('attachments');

            if ($chatType === 'individual') {
                broadcast(new ReceivedMessages($message));
            } else {
                broadcast(
                    new GroupMessageReceived($message, auth()->user()->domain)
                )->toOthers();
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $message,
                trans('message.message_created')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error sending message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function markRead($request)
    {
        try {
            $request->validate([
                'chat_type'       => 'required|in:individual,group',
                'chat_id'         => 'required|integer',
                'last_message_id' => 'required|exists:messages,id'
            ]);

            $chatType = $request->chat_type;
            $chatId   = $request->chat_id;
            $userId   = auth()->user()->id;

            if ($chatType === 'individual') {

                Message::where('is_read', 'unread')
                    ->where('user_id', $chatId)
                    ->where('receiver_id', $userId)
                    ->where('id', '<=', $request->last_message_id)
                    ->update(['is_read' => 'read']);
            } elseif ($chatType === 'group') {

                $participant = $this->verifyGroupParticipation($chatId, $userId);

                if ($participant instanceof JsonResponse) {
                    return $participant;
                }

                $participant->update([
                    'last_read_message_id' => $request->last_message_id
                ]);
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                trans('message.message_read')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error marking unified messages read: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function verifyGroupParticipation($groupId, $userId)
    {
        $participant = GroupParticipant::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                null,
                'You are not a participant of this group',
                403
            );
        }

        return $participant;
    }
}
