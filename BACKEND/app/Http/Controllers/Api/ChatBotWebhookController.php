<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Http\Requests\ChatBotRoomId;
use App\Repositories\ChatBotWebhookRepository;

class ChatBotWebhookController extends Controller
{
    private $chatBotWebhookRepository;

    public function __construct( 
    ChatbotWebhookRepository $chatBotWebhookRepository)
    {
        $this->chatBotWebhookRepository = $chatBotWebhookRepository;
    }
    
    public function fetchMessageFromRoom(ChatBotRoomId $request)
    {
        return $this->chatBotWebhookRepository->fetchMessageFromRoom($request);
    }

    public function fetchRoomId(ChatBotRoomId $request)
    {
        return $this->chatBotWebhookRepository->fetchRoomId($request);
    }
}
