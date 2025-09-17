<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetQuestionsRequest;
use App\Http\Requests\ChatbotAskQuestionRequest;
use App\Http\Requests\ChatbotConnectWithAgentRequest;
use App\Http\Requests\CustomMessageRequest;
use App\Repositories\ChatBotRepository;
use App\Http\Requests\GetCustomerMessagesRequest;
use Illuminate\Http\Request;

class CustomChatBotController extends Controller
{
    protected $chatBotRepository;

    public function __construct(ChatBotRepository $chatBotRepository)
    {
        $this->chatBotRepository = $chatBotRepository;
    }

    public function getQuestions(GetQuestionsRequest $request)
    {
        return $this->chatBotRepository->getQuestions($request);
    }

    /**
     * @OA\Post(
     *     path="/ask-question",
     *     summary="Ask a question to the chatbot",
     *     description="Sends a user question to the chatbot service and returns the chat history.",
     *     operationId="askQuestion",
     *     tags={"Chatbots"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question"},
     *             @OA\Property(property="session_id", type="string", example="abc123-session-id", description="Optional session ID for continuing conversation"),
     *             @OA\Property(property="question", type="string", example="What is Laravel?", description="User's question to the chatbot")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="object", description="Chat history or chatbot response")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function askQuestion(ChatbotAskQuestionRequest $request)
    {
        return $this->chatBotRepository->askQuestion($request);
    }

    public function connectWithAgent(Request $request)
    {
        return $this->chatBotRepository->connectWithAgent($request);
    }

    public function notifyCustomer(ChatbotConnectWithAgentRequest $request)
    {
        return $this->chatBotRepository->notifyCustomer($request);
    }

    public function storeMessage(CustomMessageRequest $request)
    {
        return $this->chatBotRepository->storeMessage($request);
    }

    public function endConversationCustomer(ChatbotConnectWithAgentRequest $request)
    {
        return $this->chatBotRepository->endConversationCustomer($request);
    }

    public function getCustomerIds(Request $request)
    {
        return $this->chatBotRepository->getCustomerIds($request);
    }

    public function getCustomerMessages(GetCustomerMessagesRequest $request)
    {
        return $this->chatBotRepository->getCustomerMessages($request);
    }
}
