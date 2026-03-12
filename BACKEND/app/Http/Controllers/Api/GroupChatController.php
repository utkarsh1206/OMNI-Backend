<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\GroupChatRepository;
use Illuminate\Http\Request;

class GroupChatController extends Controller
{
    private $groupChatRepository;

    public function __construct(GroupChatRepository $groupChatRepository)
    {
        $this->groupChatRepository = $groupChatRepository;
    }

    public function createGroup(Request $request)
    {
        return $this->groupChatRepository->createGroup($request);
    }

    public function updateGroup(Request $request, $groupId)
    {
        return $this->groupChatRepository->updateGroup($request, $groupId);
    }

    public function deleteGroup($groupId)
    {
        return $this->groupChatRepository->deleteGroup($groupId);
    }

    public function transferOwnership(Request $request, $groupId)
    {
        return $this->groupChatRepository->transferOwnership($request, $groupId);
    }

    public function addParticipants(Request $request, $groupId)
    {
        return $this->groupChatRepository->addParticipants($request, $groupId);
    }

    public function removeParticipant($groupId, $userId)
    {
        return $this->groupChatRepository->removeParticipant($groupId, $userId);
    }

    public function leaveGroup($groupId)
    {
        return $this->groupChatRepository->leaveGroup($groupId);
    }

    public function getGroupMessages(Request $request, $groupId)
    {
        return $this->groupChatRepository->getGroupMessages($request, $groupId);
    }

    public function storeGroupMessage(Request $request, $groupId)
    {
        return $this->groupChatRepository->storeGroupMessage($request, $groupId);
    }

    public function markGroupMessagesRead(Request $request, $groupId)
    {
        return $this->groupChatRepository->markGroupMessagesRead($request, $groupId);
    }

    public function deleteGroupMessage($groupId, $messageId)
    {
        return $this->groupChatRepository->deleteGroupMessage($groupId, $messageId);
    }

    public function getGroups(Request $request)
    {
        return $this->groupChatRepository->getGroups($request);
    }

    public function getGroupDetails(Request $request, $groupId)
    {
        return $this->groupChatRepository->getGroupDetails($request, $groupId);
    }

    public function getGroupMembers($groupId)
    {
        return $this->groupChatRepository->getGroupMembers($groupId);
    }

    public function updateMemberPermission(Request $request, $groupId, $userId)
    {
        return $this->groupChatRepository->updateMemberPermission($request, $groupId, $userId);
    }

    public function getUserGroupIds(Request $request)
    {
        generateLogs("debug", "Called funciton get usergroup ids");
        return $this->groupChatRepository->getUserGroupIds();
    }
}
