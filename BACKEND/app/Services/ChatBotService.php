<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatbotService
{
    protected $url;

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

            if (isset($response['session_id'])) {
                $payload = [
                    'session_id' => $response['session_id'],
                    'language' => 'en',
                ];
                $response_lang = sendHttpRequest($this->url . "/select-language", 'POST', $payload, [], false, false);
                return $response['session_id'];
            } else {
                generateLogs('error', 'ChatbotService: Failed to create session.' . json_encode($response));
                return null;
            }
        } catch (\Throwable $e) {
            generateLogs('error', 'ChatbotService: Exception during session creation.' . $e->getMessage());
            return null;
        }
    }

    public function ask(string $sessionId, string $query)
    {
        if (is_null($sessionId) || $sessionId === '') {
            $sessionId = $this->createSession();
            if (is_null($sessionId)) {
                return [
                    'answer' => 'Could not establish a session. Please try again.',
                    'sources' => [],
                    'session_id' => null
                ];
            }
        }

        try {
            $url = $this->url . "/ask";
            $payload = [
                'session_id' => $sessionId,
                'query' => $query,
            ];

            $response = sendHttpRequest($url, 'POST', $payload, [], false, false);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['answer'])) {
                $response['session_id'] = $sessionId;
                return $response;
            } else {
                generateLogs(
                    'error',
                    'ChatbotService: Failed to ask query.',
                    json_encode([
                        'session_id' => $sessionId,
                        'query' => $query,
                        'response' => $response,
                    ])
                );

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                    'session_id' => $sessionId
                ];
            }
        } catch (\Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during ask query.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
                'sources' => [],
                'session_id' => $sessionId
            ];
        }
    }

    public function getQuestions($payload)
    {
        $sessionId = $this->createSession($payload['uin'], $payload['userid']);

        try {
            $url = $this->url . "/ask";
            $payload = [
                'session_id' => $sessionId,
                'lang' => $payload['lang'],
            ];

            $response = sendHttpRequest($url, 'POST', $payload, [], false, false);

            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $response = $response->getData(true);
            }

            if (!empty($response) && isset($response['answer'])) {
                $response['session_id'] = $sessionId;
                return $response;
            } else {
                generateLogs(
                    'error',
                    'ChatbotService: Failed to get questions.',
                    json_encode([
                        'session_id' => $sessionId,
                        'query' => $query,
                        'response' => $response,
                    ])
                );

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                    'session_id' => $sessionId
                ];
            }
        } catch (\Throwable $e) {
            generateLogs(
                'error',
                'ChatbotService: Exception during ask query.',
                $e->getMessage()
            );

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
                'sources' => [],
                'session_id' => $sessionId
            ];
        }
    }
}
