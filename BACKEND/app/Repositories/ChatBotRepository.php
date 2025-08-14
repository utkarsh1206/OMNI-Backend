<?php

namespace App\Repositories;

use App\Events\CustomerChatbotRequest;
use App\Http\Response\CustomApiResponse;
use App\Http\Resources\ChatbotResource;
use App\Models\Chatbot;
use App\Services\ChatBotService;
use Exception;
use Illuminate\Http\Request;

class ChatBotRepository
{
    private $apiResponse, $chatbotService;

    public function __construct(CustomApiResponse $customApiResponse, ChatBotService $chatbotService)
    {
        $this->apiResponse = $customApiResponse;
        $this->chatbotService = $chatbotService;
    }

    public function list(Request $request)
    {
        try {
            $filters = [
                'search' => 'searchTerm',
                'question' => 'question',
                'answer' => 'answer',
                'keywords' => 'keywords',
                'status' => 'status',
            ];

            $inputs = [];
            foreach ($filters as $key => $variable) {
                $inputs[$variable] = $request->input($key, '');
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $perPage = (int)$perPage;

            $query = Chatbot::query();

            if (!empty($inputs['searchTerm'])) {
                $query->where(function ($q) use ($inputs) {
                    $searchTerm = $inputs['searchTerm'];
                    $q->where('question', 'like', "%{$searchTerm}%")
                        ->orWhere('answer', 'like', "%{$searchTerm}%")
                        ->orWhereJsonContains('keywords', $searchTerm);
                });
            }

            foreach ($filters as $key => $variable) {
                if (!empty($inputs[$variable]) && $variable !== 'searchTerm') {
                    if ($variable === 'status') {
                        $status = filter_var($inputs[$variable], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if (!is_null($status)) {
                            $query->where($variable, $status);
                        }
                    } elseif ($variable === 'keywords') {
                        if (!is_null($inputs['keywords'])) {
                            $query->whereJsonContains('keywords', $inputs['keywords']);
                        }
                    } else {
                        $query->where($variable, 'like', "%{$inputs[$variable]}%");
                    }
                }
            }
            $chatbots = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $chatbots,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Chatboats Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(Request $request)
    {
        try {
            // $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // $request->merge(['status' => $status]);
            $chatbot = Chatbot::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
                'keywords' => $request->keywords ?? [],
            ]);

            $data = new ChatbotResource($chatbot);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.chatbot_create_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $chatbot = Chatbot::findOrFail($id);

            $chatbot->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $status,
                'keywords' => $request->keywords ?? [],
            ]);

            $data = new ChatbotResource($chatbot);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.chatbot_update_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $chatbot = Chatbot::findOrFail($id);

            $chatbot->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $id,
                trans('message.chatbot_delete_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function askQuestion(Request $request)
    {
        try {
            $sessionId = $request->session_id ?? '';
            $query = $request->question;
            $chatHistory = $this->chatbotService->ask($sessionId, $query);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $chatHistory,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing of custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function connectWithAgent(Request $request)
    {
        try {
            $boradCastData = [
                'session_id' => $request->session_id,
                'customer_id' => 2
            ];
            broadcast(new CustomerChatbotRequest($boradCastData));
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating webhook for Custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
