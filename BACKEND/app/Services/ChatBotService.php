<?php

namespace App\Services;

use App\Models\ChatbotSession;
use Illuminate\Http\JsonResponse;
use Throwable;

class ChatBotService
{
    protected $url, $apiToken;

    public function __construct()
    {
        $this->url = config('omni.CHATBOT_URL');
    }

    protected function createSession($uin, $customerId)
    {
        try {
            $url = $this->url . "/create-session";

            $payload = [
                'tenant_id' => $uin,
                'customer_id' => $customerId,
            ];

            $response = sendHttpRequest($url, 'POST', $payload, [], false, false);

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }
            if (!empty($response['session_id']) && !empty($response['session_api_token'])) {
                ChatbotSession::updateOrCreate(
                    [
                        'tenant_id'   => $response['tenant_id'],
                        'customer_id' => $response['customer_id'],
                    ],
                    [
                        'session_id'        => $response['session_id'],
                        'session_api_token' => $response['session_api_token'],
                        'customer_token'    => $response['customer_token'] ?? null,
                    ]
                );

                $this->apiToken = $response['session_api_token'];

                return $response;
            } else {
                generateLogs('error', 'ChatbotService: Failed to create session.' . json_encode($response));
                return $response;   
            }
        } catch (Throwable $e) {
            generateLogs('error', 'ChatbotService: Exception during session creation.' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create session',
            ], 500);
        }
    }

    public function getQuestions($payload)
    {
        try {
            $sessionId = $this->createSession($payload['uin'], $payload['userid']);
        
            if (!isset($sessionId['session_id'])) {
                return [
                    'status_code' => $sessionId['status_code'] ?? $sessionId['code'],
                    'message'     => $sessionId['message'] ?? 'Failed to create session',
                ];
            }
        
            $url = $this->url . "/get-questions?session_id=" . $sessionId['session_id'] . "&lang=" . $payload['lang'];
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ];

            $response = sendHttpRequest($url, 'GET', [], $headers, false, false, true);

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['questions'])) {
                return $response;
            }

            generateLogs('error', 'ChatbotService: Failed to get questions API.' . json_encode($response));
            return [
                'status_code' => $response['status_code'] ?? $response['code'],
                'message'     => $response['message'] ?? 'Failed to get question',
            ];
            // else {
            //     generateLogs('error', 'ChatbotService: Failed to get questions API.' . json_encode($response));
            //     if (!isset($response['session_id'])) {
            //         return [
            //             'status_code' => $response['status_code'] ?? $response['code'],
            //             'message'     => $response['message'] ?? 'Failed to get questions',
            //         ];
            //     }
            //     // return $response;   
            // }
        } catch (Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during get questions API.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
            ];
        }
    }

    public function ask($apiPayload)
    {
        try {
            $chatSession = ChatbotSession::where('session_id', $apiPayload['session_id'])->first();

            $url = $this->url . "/ask?session_id=" . $apiPayload['session_id'] . "&lang=" . $apiPayload['lang'];;

            $payload = [
                'query' => $apiPayload['query'],
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $chatSession->session_api_token,
            ];

            $response = sendHttpRequest($url, 'POST', $payload, $headers, false, false, false);

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['answer'], $response['sources'])) {
                return $response;
            }

            generateLogs('error', 'ChatbotService: Failed to ask questions API.' . json_encode($response));
            return [
                'status_code' => $response['status_code'] ?? $response['code'],
                'message'     => $response['message'] ?? 'Failed to ask question',
            ];

            // if (!empty($response)) {
            //     return $response;
            // } else {
            //     generateLogs(
            //         'error',
            //         'ChatbotService: Failed to ask API.',
            //         json_encode([
            //             'response' => $response,
            //         ])
            //     );

            //     return [
            //         'answer' => 'Sorry, I could not process your request at this time.',
            //         'sources' => [],
            //     ];
            // }
        } catch (Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during ask API.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
            ];
        }
    }

    public function fetchHistory($request)
    {
        try {
            $chatSession = ChatbotSession::where('customer_id', $request->customer_id)->first();

            $url = $this->url . "/chat-history/?tenant_id=" . $chatSession->tenant_id . "&customer_id=" . $chatSession->customer_id;

            $headers = [
                'Authorization' => 'Bearer ' . $chatSession->customer_token,
            ];

            $response = sendHttpRequest($url, 'GET', [], $headers, false, false, true);

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['history'])) {
                return $response;
            }

            generateLogs('error', 'ChatbotService: Failed to fetch history API.' . json_encode($response));
            return [
                'status_code' => $response['status_code'] ?? $response['code'],
                'message'     => $response['message'] ?? 'Failed to fetch history',
            ];

            // if (!empty($response)) {
            //     return $response;
            // } else {
            //     generateLogs(
            //         'error',
            //         'ChatbotService: Failed to ask query.',
            //         json_encode([
            //             'response' => $response,
            //         ])
            //     );

            //     return [
            //         'answer' => 'Sorry, I could not process your request at this time.',
            //         'sources' => [],
            //     ];
            // }
        } catch (Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during fetch history.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
            ];
        }
    }
}
