<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat360Service;
use App\Models\Detail;
use App\Http\Requests\ChatBotRoomId;
use App\Repositories\ChatBotWebhookRepository;

class ChatBotWebhookController extends Controller
{
    private $chat360Service;
    
    private $chatBotWebhookRepository;

    public function __construct(Chat360Service $chat360Service, 
    ChatbotWebhookRepository $chatBotWebhookRepository)
    {
        $this->chat360Service = $chat360Service;
        $this->chatBotWebhookRepository = $chatBotWebhookRepository;
    }
    
    public function fetchMessageFromRoom(ChatBotRoomId $request, Detail $detail)
    {
        return $this->chatBotWebhookRepository->fetchMessageFromRoom($request, $detail);
    }
}
