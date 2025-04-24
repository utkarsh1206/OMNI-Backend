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
        
            $messages  = Message::where("user_id", $userId);
            $messages = $messages->get();
        
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
            $userId = $request->input('user_id');
            $file = uploadAttachment($request,"attachment","attachment");
            $message = Message::Create([
                "text" => $request->text,
                "user_id" => $userId,
                "attachment" => $file['path']
            ]);
            SendMessage::dispatch($message);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
