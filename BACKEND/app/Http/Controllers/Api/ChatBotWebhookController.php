<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat360Service;
use Illuminate\Http\Request;
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
    
    public function index(Request $request)
    {
        generateLogs("info", "chatwebhook Log :",$request->all());

        if($request->source == "chat360"){ 
            $response = $this->chat360Service->login();   
            $chatHistory = $this->chat360Service->getChatHistory($response["access"], $request->roomId);
            dd($chatHistory);
        }
    }

    public function fetchMessageFromRoom(ChatBotRoomId $request)
    {
        return $this->chatBotWebhookRepository->fetchMessageFromRoom($request);
    }
}
