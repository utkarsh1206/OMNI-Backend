<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detail;
use App\Http\Requests\ChatBotRoomId;
use App\Repositories\ChatBotWebhookRepository;
use Illuminate\Http\Request;
class ChatBotWebhookController extends Controller
{
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

    public function fetchChatResponse(Request $request, Detail $detail)
    {
       generateLogs('info', 'Incoming Data: ' . json_encode($request->all()));
        // return $this->chatBotWebhookRepository->fetchMessageFromRoom($request, $detail);
    }
}
