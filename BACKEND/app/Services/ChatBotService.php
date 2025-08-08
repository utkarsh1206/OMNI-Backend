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

    // protected function createSession()
    // {
    //     try {
    //         $response = Http::post($this->url . "/create-session");

    //         if ($response->successful()) {
    //             return $response->json('session_id');
    //         } else {
    //             generateLogs('error', 'ChatbotService: Failed to create session.', [
    //                 'status' => $response->status(),
    //                 'response' => $response->body(),
    //             ]);
    //             return null;
    //         }
    //     } catch (\Throwable $e) {
    //         generateLogs('error', 'ChatbotService: Exception during session creation.', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         return null;
    //     }
    // }

    // public function ask(string $sessionId, string $query)
    // {
    //     if (is_null($sessionId)) {
    //         $sessionId = $this->createSession();
    //         if (is_null($sessionId)) {
    //             return [
    //                 'answer' => 'Could not establish a session. Please try again.',
    //                 'sources' => [],
    //                 'session_id' => null
    //             ];
    //         }
    //     }

    //     try {
    //         $response = Http::post($this->url . "/ask", [
    //             'session_id' => $sessionId,
    //             'query' => $query,
    //         ]);

    //         if ($response->successful()) {
    //             $responseData = $response->json();

    //             $responseData['session_id'] = $sessionId;

    //             return $responseData;
    //         } else {
    //             generateLogs('error', 'ChatbotService: Failed to ask query.', [
    //                 'session_id' => $sessionId,
    //                 'query' => $query,
    //                 'status' => $response->status(),
    //                 'response' => $response->body(),
    //             ]);
    //             return [
    //                 'answer' => 'Sorry, I could not process your request at this time.',
    //                 'sources' => [],
    //                 'session_id' => $sessionId
    //             ];
    //         }
    //     } catch (\Throwable $e) {
    //         generateLogs('error', 'ChatbotService: Exception during ask query.', [
    //             'session_id' => $sessionId,
    //             'query' => $query,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);
    //         return [
    //             'answer' => 'An unexpected error occurred. Please try again.',
    //             'sources' => [],
    //             'session_id' => $sessionId
    //         ];
    //     }
    // }

    protected function createSession()
    {
        try {
            $url = $this->url . "/create-session";

            $response = sendHttpRequest($url, 'POST', [], [], false, false);

            if (isset($response['session_id'])) {
                return $response['session_id'];
            } else {
                generateLogs('error', 'ChatbotService: Failed to create session.', [
                    'response' => $response,
                ]);
                return null;
            }
        } catch (\Throwable $e) {
            generateLogs('error', 'ChatbotService: Exception during session creation.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    public function ask(string $sessionId, string $query)
    {
        if (is_null($sessionId)) {
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

            if (!empty($response) && isset($response['answer'])) {
                $response['session_id'] = $sessionId;
                return $response;
            } else {
                generateLogs('error', 'ChatbotService: Failed to ask query.', [
                    'session_id' => $sessionId,
                    'query' => $query,
                    'response' => $response,
                ]);

                return [
                    'answer' => 'Sorry, I could not process your request at this time.',
                    'sources' => [],
                    'session_id' => $sessionId
                ];
            }
        } catch (\Throwable $e) {
            generateLogs('error', 'ChatbotService: Exception during ask query.', [
                'session_id' => $sessionId,
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'answer' => 'An unexpected error occurred. Please try again.',
                'sources' => [],
                'session_id' => $sessionId
            ];
        }
    }
}
