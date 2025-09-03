<?php

namespace App\Services;

use Throwable;
use Illuminate\Http\JsonResponse;

class ChatBotService
{
    protected $url, $apiToken, $tanentId, $customerId, $customerToken;

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
                $this->apiToken = $response['session_api_token'];
                $this->tanentId = $response['tenant_id'];
                $this->customerId = $response['customer_id'];
                $this->customerToken = $response['customer_token'];
                return $response;
            } else {
                generateLogs('error', 'ChatbotService: Failed to create session.' . json_encode($response));
                return null;
            }
            // if (isset($response['session_id'])) {
            //     $payload = [
            //         'session_id' => $response['session_id'],
            //         'language' => 'en',
            //     ];
            //     $response_lang = sendHttpRequest($this->url . "/select-language", 'POST', $payload, [], false, false);
            //     return $response['session_id'];
            // } else {
            //     generateLogs('error', 'ChatbotService: Failed to create session.' . json_encode($response));
            //     return null;
            // }
        } catch (Throwable $e) {
            generateLogs('error', 'ChatbotService: Exception during session creation.' . $e->getMessage());
            return null;
        }
    }

    public function getQuestions($payload)
    {
        try {
            $sessionId = $this->createSession($payload['uin'], $payload['userid']);

            $url = $this->url . "/get-questions?session_id=" . $sessionId['session_id'] . "&language=" . $payload['lang'];

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ];

            $response = sendHttpRequest($url, 'GET', [], $headers, false, false, true);
        
            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['questions'])) {
                return $response;
            } else {
                generateLogs(
                    'error',
                    'ChatbotService: Failed to get questions API.',
                    json_encode([
                        'session_id' => $sessionId,
                    ])
                );

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                    'session_id' => $sessionId
                ];
            }
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
            $url = $this->url . "/ask?session_id=" . $apiPayload['session_id'] . "&language=" . $apiPayload['lang'];;

            $payload = [
                'query' => $apiPayload['query'],
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $this->customerToken,
            ];

            $response = sendHttpRequest($url, 'POST', $payload, $headers, false, false, false);
        
            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response)) {
                return $response;
            } else {
                generateLogs(
                    'error',
                    'ChatbotService: Failed to ask API.',
                    json_encode([
                        'response' => $response,
                    ])
                );

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                ];
            }
        } catch (Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during ask API.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
                'sources' => [],
            ];
        }
    }

    public function fetchHistory()
    {
        try {
            $url = $this->url . "/chat-history/?tanent_id=" . $this->tanentId . "&customer_id=" . $this->customerId;

            $headers = [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ];

            $response = sendHttpRequest($url, 'GET', [], $headers, false, false, true);

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response)) {
                return $response;
            } else {
                generateLogs(
                    'error',
                    'ChatbotService: Failed to ask query.',
                    json_encode([
                        'response' => $response,
                    ])
                );

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                ];
            }
        } catch (Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during ask query.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
                'sources' => [],
            ];
        }
    }
}
