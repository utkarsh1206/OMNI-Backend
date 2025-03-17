<?php

namespace App\Repositories;

use App\Models\Chatbot;
use Illuminate\Http\Request;
use App\Http\Resources\ChatbotResource;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

class ChatbotRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {

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

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

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

        if ($page === 'all') {
            $chatbots = $query->get();
        } else {
            $chatbots = $query->paginate($perPage);
        }

        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            $chatbots,
            trans('message.data_fetch')
        );
    }

    public function create(Request $request)
    {
        try {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // $request->merge(['status' => $status]);

            $chatbot = Chatbot::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
                'keywords' => $request->keywords ?? [],
            ]);

            $data = new ChatbotResource($chatbot);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.chatbot_create_success'));
        } catch (Exception $ex) {
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.chatbot_update_success'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $chatbot = Chatbot::findOrFail($id);

            $chatbot->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $id, trans('message.chatbot_delete_success'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
