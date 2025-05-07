<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Jobs\SendMessage;
use App\Services\Chat360Service;

class MessageRepository
{
    private $apiResponse;

    private $chat360Service;

    public function __construct(
        CustomApiResponse $customApiResponse,
        Chat360Service $chat360Service
    ) {
        $this->apiResponse = $customApiResponse;
        $this->chat360Service = $chat360Service;
    }

    public function getMessages()
    {
        try {
            $userId = auth()->user()->id;
            $messages  = Message::where("type", "user_chat")->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage($request)
    {
        // File upload logic is remaining here. We need to make file upload logic in common place
        try {
            $file = uploadAttachment($request, "attachment", "attachment");
        
            $message = Message::Create([
                "user_id" => auth()->user()->id,
                "text" => $request->text,
                "is_read" => 'unread',
                "type" => 'user_chat',
                "attachment" => $file['path'] ?? null,
                "sender" => 'agent',
            ]);

            if ($message) {
                $this->chat360Service->sendMessage('text', ['body' => $request->text]);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
