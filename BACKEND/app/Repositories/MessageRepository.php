<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Jobs\SendMessage;

class MessageRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getMessages()
    {
        try {
            $userId = auth()->user()->id;
           // where("user_id", $userId)->
            $messages  = Message::get();
          
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
            $file = uploadAttachment($request,"attachment","attachment");
            $message = Message::Create([
                "user_id" => auth()->user()->id,
                "text" => $request->text,
                "is_read" => 'unread',
                "attachment" => $file['path'] ?? null,
                "sender" => 'agent',
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
