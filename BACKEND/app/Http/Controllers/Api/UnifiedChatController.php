<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UnifiedChatRepository;
use Illuminate\Http\Request;

class UnifiedChatController extends Controller
{
    private $unifiedChatRepository;

    public function __construct(UnifiedChatRepository $unifiedChatRepository)
    {
        $this->unifiedChatRepository = $unifiedChatRepository;
    }

    public function getChats(Request $request)
    {
        return $this->unifiedChatRepository->getChats($request);
    }

    public function getMessages(Request $request)
    {
        return $this->unifiedChatRepository->getMessages($request);
    }

    public function sendMessage(Request $request)
    {
        return $this->unifiedChatRepository->sendMessage($request);
    }

    public function markRead(Request $request)
    {
        return $this->unifiedChatRepository->markRead($request);
    }
}
