<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Response\CustomApiResponse;
use Exception;

class MessageRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getMessages(Request $request)
    {
        try {
            $providerName = $request->input('provider_name', '');
            $method = $request->input('method', '');
            $enabled = $request->input('enabled', '');
            $search = $request->input('search', '');

            if ($method) {
                $chatBotConfigurations = ChatBotConfiguration::where('config_value', $method);
            } elseif ($enabled) {
                $chatBotConfigurations = ChatBotConfiguration::where('config_value', $enabled);
            } else {
                $chatBotConfigurations = ChatBotConfiguration::query();
            }

            if ($search) {
                $chatBotConfigurations = $chatBotConfigurations->orWhere('config_value', 'like', '%' . $search . '%');
            }

            $chatBotConfigurations = $chatBotConfigurations->pluck("provider_id");
            $configurations  = ChatBotProvider::whereIn("id", $chatBotConfigurations);

            if ($providerName) {
                $configurations =  $configurations->where('name', '=', $providerName);
            }

            if ($search) {
                $configurations = $configurations->orWhere('name', 'like', '%' . $search . '%');
            }

            $configurations = $configurations->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $this->formatListingData($configurations, $method, $enabled), trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Chatboat Configuration Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage($request)
    {
        // File upload logic is remaining here. We need to make file upload logic in common place
        try {
            $message = Message::Create([
                "text" => $request->text,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
