<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Chat360Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatBootWebhookController extends Controller
{
    private $chat360Service;
    public function __construct(Chat360Service $chat360Service)
    {
        $this->chat360Service = $chat360Service;
    }
    public function index(Request $request)
    {
        Log::info("chatwebhook Log :",$request->all());
        if($request->source == "chat360"){ 
            $response = $this->chat360Service->login();   
            $chatHistory = $this->chat360Service->getChatHistory($response["access"],$request->roomId);
            dd($chatHistory);
        }
        
    }

    public function checkAuthWebhook(Request $request)
    {
        
    }

    
}
