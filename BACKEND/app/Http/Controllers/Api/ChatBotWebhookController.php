<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Http\Requests\ChatBotRoomId;
use App\Repositories\ChatBotWebhookRepository;

class ChatBotWebhookController extends Controller
{
    private $chat360Service;
    
    private $chatBotWebhookRepository;

    public function __construct( 
    ChatbotWebhookRepository $chatBotWebhookRepository)
    {
        $this->chatBotWebhookRepository = $chatBotWebhookRepository;
    }
    
    public function fetchMessageFromRoom(ChatBotRoomId $request, Detail $detail)
    {
        return $this->chatBotWebhookRepository->fetchMessageFromRoom($request, $detail);
    }
}
