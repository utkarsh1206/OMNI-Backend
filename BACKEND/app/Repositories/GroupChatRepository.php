<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\Group;
use App\Models\GroupParticipant;
use App\Models\Users;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Events\GroupMemberAdded;
use App\Events\GroupMemberRemoved;
use App\Events\GroupMessageReceived;
use App\Models\Message;
use App\Models\MessageAttachment;

class GroupChatRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    private function ensureParticipant($groupId)
    {
        // $exists = GroupParticipant::where('group_id', $groupId)
        //     ->where('user_id', auth()->user()->id)
        //     ->whereNull('left_at')
        //     ->exists();

        // if (!$exists) {
        //     throw new Exception('You are not a participant of this group');
        // }

        $participant = GroupParticipant::where('group_id', $groupId)
            ->where('user_id', auth()->user()->id)
            ->whereNull('left_at')
            ->first();

        if (!$participant) {
            // throw new Exception('You are not a participant of this group');
            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                null,
                'You are not a participant of this group',
                403
            );
        }

        return $participant;
    }

    // private function validateMessagePermission(Group $group, GroupParticipant $participant)
    // {
    //     if ($group->type === 'broadcast') {
    //         if (!in_array($participant->role, ['owner', 'admin'])) {
    //             throw new Exception('Only owner/admin can send messages in broadcast group');
    //         }
    //     } else {
    //         if (!$participant->can_send_message) {
    //             throw new Exception('You do not have permission to send messages in this group');
    //         }
    //     }
    // }
    private function validateMessagePermission(Group $group, GroupParticipant $participant)
    {
        if ($group->type === 'broadcast') {
            if (!in_array($participant->role, ['owner', 'admin'])) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'Only owner/admin can send messages in broadcast group',
                    403
                );
            }
        } else {
            if (!$participant->can_send_message) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    'You do not have permission to send messages in this group',
                    403
                );
            }
        }

        return true;
    }

    /**
     * Authorization helper
     */
    private function authorizeAdminOrOwner(Group $group)
    {
        $participant = GroupParticipant::where('group_id', $group->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (!$participant || !in_array($participant->role, ['owner', 'admin'])) {
            // throw new Exception('Unauthorized action');
            return $this->apiResponse->getResponseStructure(
                trans('message.error'),
                null,
                'You are not authorized to work on this group',
                403
            );
        }

        return true;
    }

    /**
     * Create Group
     */
    public function createGroup($request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:normal,broadcast',
                'participants' => 'array|max:99',
                'participants.*' => 'exists:users,id'
            ]);

            $userId = auth()->user()->id;

            $group = Group::create([
                'name' => $request->name,
                'owner_id' => $userId,
                'created_by' => $userId,
                'type' => $request->type
            ]);

            // Owner as participant
            GroupParticipant::create([
                'group_id' => $group->id,
                'user_id' => $userId,
                'role' => 'owner',
                'can_send_message' => true,
                'joined_at' => Carbon::now()
            ]);

            // Add participants
            if (!empty($request->participants)) {
                foreach ($request->participants as $participantId) {
                    if ($participantId == $userId) {
                        continue;
                    }

                    GroupParticipant::create([
                        'group_id' => $group->id,
                        'user_id' => $participantId,
                        'role' => 'member',
                        'can_send_message' => $request->type === 'normal' ? true : false,
                        'joined_at' => Carbon::now()
                    ]);
                }
            }

            DB::commit();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $group->load('participants'),
                'Group created successfully'
            );
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error in creating group', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Update Group Name
     */
    public function updateGroup($request, $groupId)
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|in:normal,broadcast'
            ]);

            $group = Group::findOrFail($groupId);

            $authCheck = $this->authorizeAdminOrOwner($group);

            if ($authCheck !== true) {
                return $authCheck;
            }

            $group->update($request->only(['name', 'type']));

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $group,
                'Group updated successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating group', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Delete Group (Owner/Admin)
     */
    public function deleteGroup($groupId)
    {
        try {
            $group = Group::findOrFail($groupId);

            $authCheck = $this->authorizeAdminOrOwner($group);

            if ($authCheck !== true) {
                return $authCheck;
            }

            $group->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'Group deleted successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting group', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Transfer Ownership
     */
    public function transferOwnership($request, $groupId)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'new_owner_id' => 'required|exists:users,id'
            ]);

            $group = Group::findOrFail($groupId);
            $currentUserId = auth()->user()->id;

            if ($group->owner_id != $currentUserId) {
                throw new Exception('Only group owner can transfer ownership');
            }

            $newOwner = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', $request->new_owner_id)
                ->whereNull('left_at')
                ->first();

            if (!$newOwner) {
                throw new Exception('New owner must be a group participant');
            }

            // Update old owner
            GroupParticipant::where('group_id', $groupId)
                ->where('user_id', $currentUserId)
                ->update(['role' => 'admin', 'can_send_message' => true]);

            // Update new owner
            $newOwner->update([
                'role' => 'owner',
                'can_send_message' => true
            ]);

            // Update group table
            $group->update(['owner_id' => $request->new_owner_id]);

            DB::commit();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $group->load('participants'),
                'Ownership transferred successfully'
            );
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error in transferring ownership', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    // public function addParticipants($request, $groupId)
    // {
    //     try {
    //         $request->validate([
    //             'participants' => 'required|array',
    //             'participants.*' => 'exists:users,id'
    //         ]);

    //         $group = Group::findOrFail($groupId);
    //         $authCheck = $this->authorizeAdminOrOwner($group);

    //         if ($authCheck !== true) {
    //             return $authCheck;
    //         }

    //         $currentCount = GroupParticipant::where('group_id', $groupId)
    //             ->whereNull('left_at')
    //             ->count();

    //         $newCount = count($request->participants);

    //         if (($currentCount + $newCount) > 100) {
    //             throw new Exception('Group member limit exceeded');
    //         }

    //         foreach ($request->participants as $userId) {

    //             $exists = GroupParticipant::where('group_id', $groupId)
    //                 ->where('user_id', $userId)
    //                 ->whereNull('left_at')
    //                 ->exists();

    //             if ($exists) {
    //                 continue;
    //             }

    //             GroupParticipant::create([
    //                 'group_id' => $groupId,
    //                 'user_id' => $userId,
    //                 'role' => 'member',
    //                 'joined_at' => Carbon::now()
    //             ]);

    //             broadcast(new GroupMemberAdded($groupId, $userId, auth()->user()->domain));
    //         }

    //         return $this->apiResponse->getResponseStructure(
    //             trans('message.success'),
    //             '',
    //             'Participants added successfully'
    //         );
    //     } catch (Exception $ex) {
    //         generateLogs('error', 'Error in adding participants', $ex->getMessage());
    //         return $this->apiResponse->handleAndResponseException($ex);
    //     }
    // }

    public function addParticipants($request, $groupId)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'participants' => 'required|array',
                'participants.*.id' => 'required|exists:users,id',
                'participants.*.can_send_message' => 'nullable|boolean'
            ]);

            $group = Group::findOrFail($groupId);
            $authCheck = $this->authorizeAdminOrOwner($group);

            if ($authCheck !== true) return $authCheck;

            // 1. Get IDs of people already in the group
            $existingMemberIds = GroupParticipant::where('group_id', $groupId)
                ->whereNull('left_at')
                ->pluck('user_id')
                ->toArray();

            // 2. Identify brand NEW members (not already in existingMemberIds)
            $incomingParticipants = collect($request->participants);
            $newMemberCount = $incomingParticipants->reject(fn($p) => in_array($p['id'], $existingMemberIds))->count();

            // 3. Perform the count check accurately
            $currentCount = count($existingMemberIds);
            if (($currentCount + $newMemberCount) > 100) {
                throw new Exception('Group member limit exceeded');
            }

            $defaultPermission = ($group->type === 'normal');
            // 4. Create the records
            foreach ($incomingParticipants as $participantData) {
                $userId = $participantData['id'];
                // Default permission based on group type if not explicitly provided
                $canSend = $participantData['can_send_message'] ?? $defaultPermission;

                $participant = GroupParticipant::updateOrCreate(
                    ['group_id' => $groupId, 'user_id' => $userId],
                    [
                        'can_send_message' => $canSend,
                        'role' => in_array($userId, $existingMemberIds) ? 'member' : 'member', // Keep role logic simple
                        'joined_at' => Carbon::now(),
                        'left_at' => null // Re-activate if they had previously left
                    ]
                );

                // Only broadcast if they were NOT already in the group
                if (!in_array($userId, $existingMemberIds)) {
                    broadcast(new GroupMemberAdded($groupId, $userId, auth()->user()->domain));
                }
            }

            DB::commit();
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'Participants added successfully'
            );
        } catch (Exception $ex) {
            DB::rollBack();
            generateLogs('error', 'Error in adding participants', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function removeParticipant($groupId, $userId)
    {
        try {
            $group = Group::findOrFail($groupId);
            $authCheck = $this->authorizeAdminOrOwner($group);

            if ($authCheck !== true) {
                return $authCheck;
            }

            $participant = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->first();

            if (!$participant) {
                throw new Exception('User is not a group participant');
            }

            if ($participant->role === 'owner') {
                throw new Exception('Owner cannot be removed');
            }

            $participant->update([
                'left_at' => Carbon::now()
            ]);

            broadcast(new GroupMemberRemoved($groupId, $userId, auth()->user()->domain));

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'Participant removed successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in removing participant', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function leaveGroup($groupId)
    {
        try {
            $group = Group::findOrFail($groupId);
            $userId = auth()->user()->id;

            $participant = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->first();

            if (!$participant) {
                throw new Exception('You are not a participant of this group');
            }

            if ($participant->role === 'owner') {
                throw new Exception('Owner must transfer ownership before leaving the group');
            }

            $participant->update([
                'left_at' => Carbon::now()
            ]);

            broadcast(new GroupMemberRemoved($groupId, $userId, auth()->user()->domain));

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'You have left the group'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in leaving group', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getGroupMessages($request, $groupId)
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $check = $this->ensureParticipant($groupId);
            if (!($check instanceof GroupParticipant)) {
                return $check;
            }

            $messages = Message::where('group_id', $groupId)
                ->where('type', 'agent_chat')
                ->orderBy('created_at', 'desc');

            if ($search) {
                $messages->where('text', 'like', "%{$search}%");
            }

            $data = ($page == config('omni.ALL_PAGE'))
                ? $messages->get()
                : $messages->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching group messages', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeGroupMessage($request, $groupId)
    {
        try {
            // $this->ensureParticipant($groupId);

            $participant = $this->ensureParticipant($groupId);
            if (!($participant instanceof GroupParticipant)) {
                return $participant;
            }

            $group = Group::findOrFail($groupId);

            $this->validateMessagePermission($group, $participant);

            $files = uploadAttachment($request, "attachment", "attachment");

            $message = Message::create([
                'user_id' => auth()->user()->id,
                'group_id' => $groupId,
                'type' => 'agent_chat',
                'text' => $request->text,
                'is_read' => 'unread',
                'sender' => 'agent',
                'external_message_time' => Carbon::now(),
            ]);

            if ($files) {
                $fileData = [];
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

            // // Broadcast to all participants
            // $participants = GroupParticipant::where('group_id', $groupId)
            //     ->whereNull('left_at')
            //     ->pluck('user_id');

            // foreach ($participants as $userId) {
            //     broadcast(new GroupMessageReceived($message, $userId, auth()->user()->domain));
            // }
            broadcast(new GroupMessageReceived($message, auth()->user()->domain))->toOthers();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $message,
                trans('message.message_created')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error sending group message', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function markGroupMessagesRead($request, $groupId)
    {
        try {
            $request->validate([
                'last_message_id' => 'required|exists:messages,id'
            ]);

            $participant = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', auth()->user()->id)
                ->whereNull('left_at')
                ->firstOrFail();

            $participant->update([
                'last_read_message_id' => $request->last_message_id
            ]);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                trans('message.message_read')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error marking group messages read', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
    public function deleteGroupMessage($groupId, $messageId)
    {
        try {
            $participant = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', auth()->user()->id)
                ->whereIn('role', ['admin', 'owner'])
                ->first();

            if (!$participant) {
                throw new Exception('Unauthorized action');
            }

            $message = Message::where('group_id', $groupId)
                ->where('id', $messageId)
                ->firstOrFail();

            MessageAttachment::where('message_id', $messageId)->delete();
            $message->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'Message deleted successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error deleting group message', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getGroups($request)
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $userId = auth()->user()->id;

            $groupsQuery = Group::select('groups.*')
                ->join('group_participants as gp', function ($join) use ($userId) {
                    $join->on('gp.group_id', '=', 'groups.id')
                        ->where('gp.user_id', $userId)
                        ->whereNull('gp.left_at');
                })
                ->withCount([
                    'participants as participant_count' => function ($q) {
                        $q->whereNull('left_at');
                    }
                ])
                ->addSelect([
                    'last_message_time' => Message::select('created_at')
                        ->whereColumn('messages.group_id', 'groups.id')
                        ->orderByDesc('created_at')
                        ->limit(1),
                    'last_message_text' => Message::select('text')
                        ->whereColumn('messages.group_id', 'groups.id')
                        ->orderByDesc('created_at')
                        ->limit(1)
                ])
                ->when($search, function ($query) use ($search) {
                    $query->where('groups.name', 'like', "%{$search}%");
                })
                ->orderByDesc('last_message_time');

            $results = ($page == config('omni.ALL_PAGE'))
                ? $groupsQuery->get()
                : $groupsQuery->paginate($perPage);

            $collection = ($page == config('omni.ALL_PAGE'))
                ? $results
                : $results->getCollection();

            $data = $collection->map(function ($group) use ($userId) {

                $participant = GroupParticipant::where('group_id', $group->id)
                    ->where('user_id', $userId)
                    ->first();

                $unreadCount = Message::where('group_id', $group->id)
                    ->where('id', '>', $participant->last_read_message_id ?? 0)
                    ->count();

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'type' => $group->type,
                    'participant_count' => $group->participant_count,
                    'last_message' => $group->last_message_text,
                    'last_message_time' => $group->last_message_time,
                    'unread_count' => $unreadCount,
                ];
            });

            if ($page == config('omni.ALL_PAGE')) {
                $finalResponse = $data;
            } else {
                $results->setCollection($data);
                $finalResponse = $results;
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $finalResponse,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching group list', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function updateMemberPermission($request, $groupId, $userId)
    {
        try {
            $request->validate([
                'can_send_message' => 'required|boolean'
            ]);

            $group = Group::findOrFail($groupId);

            if ($group->type === 'broadcast') {
                throw new Exception('Cannot modify permissions in broadcast group');
            }

            $authCheck = $this->authorizeAdminOrOwner($group);

            if ($authCheck !== true) {
                return $authCheck;
            }

            $participant = GroupParticipant::where('group_id', $groupId)
                ->where('user_id', $userId)
                ->whereNull('left_at')
                ->firstOrFail();

            if ($participant->role === 'owner') {
                throw new Exception('Cannot modify owner permission');
            }

            $participant->update([
                'can_send_message' => $request->can_send_message
            ]);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                'Permission updated successfully'
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error updating member permission', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getGroupMembers($groupId)
    {
        try {
            $check = $this->ensureParticipant($groupId);
            if (!($check instanceof GroupParticipant)) {
                return $check;
            }

            $members = GroupParticipant::with('user')
                ->where('group_id', $groupId)
                ->whereNull('left_at')
                ->get()
                ->map(function ($participant) {
                    return [
                        'user_id' => $participant->user_id,
                        'name' => $participant->user->first_name . ' ' . $participant->user->last_name,
                        'role' => $participant->role,
                        'can_send_message' => $participant->can_send_message
                    ];
                });

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $members,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching group members', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getUserGroupIds()
    {
        try {
            $userId = auth()->user()->id;

            $groupIds = GroupParticipant::where('user_id', $userId)
                ->whereNull('left_at')
                ->whereHas('group', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->pluck('group_id');

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $groupIds,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching user group ids', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
