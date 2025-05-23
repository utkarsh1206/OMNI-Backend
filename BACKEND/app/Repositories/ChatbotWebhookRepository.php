<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Exception;
use App\Events\CustomerChatRequest;

class ChatbotWebhookRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function fetchMessageFromRoom(Request $request, $detail)
    {
        try {                
            $boradCastData = [
                'room_id' => $request->room_id,
            ];
            broadcast(new CustomerChatRequest($boradCastData, $detail->domain));    
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Storing Messages of Room: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

}
