<?php

namespace App\Services;

use App\Enums\ProviderType;
use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\Http;
use Exception;

class Chat360Service
{
    protected $email;
    protected $password;
    protected $url;
    protected $apiKey;
    protected $fromPhone;
    protected $retryAttempts;
    protected $retryWait;
    protected $timeout;

    public function __construct()
    {
        $this->email =  config('omni.CHAT_360_EMAIL');
        $this->password =  config('omni.CHAT_360_PASSWORD');
        $this->url =  config('omni.CHAT_360_URL');
        $this->apiKey = config('omni.CHAT_360_API_KEY');
        $this->fromPhone = config('omni.CHAT_FROM_PHONE');
        $this->retryAttempts =  config('omni.RETRY_ATTEMPTS');
        $this->retryWait = config('omni.RETRY_WAIT');
        $this->timeout = config('omni.TIMEOUT');
    }

    public function login()
    {
        try {
            $apiData = $this->getApiConfig('Chat360 Auth Token');
            $method = $apiData['method'] ?? 'post';
            $url = $apiData['url'] ?? $this->url . 'api/auth/login';
            $email = $apiData['params']['email'] ?? $this->email;
            $password = $apiData['params']['password'] ?? $this->password;

            $response = $this->httpClient()->$method($url, [
                'email' => $email,
                'password' => $password,
            ]);
            if (! $response->ok()) {
                generateLogs('error', 'Failed for Authentication for Chat360.');
                return null;
            }

            // if ($response->status() == $apiData['success_code']) {

            // }

            return $response->json();
        } catch (Exception $e) {
            generateLogs('error', 'Error in Login API Calling : ', $e->getMessage());
            return $e->getMessage();
        }
    }

    public function getAccessToken()
    {
        try {
            $loginResponse = $this->login();

            return $loginResponse['access'] ?? null;
        } catch (Exception $e) {
            generateLogs('error', 'Error in Login API Calling : ', $e->getMessage());
            return $e->getMessage();
        }
    }

    public function getChatHistory($roomId)
    {
        try {
            $apiData = $this->getApiConfig('Chat360 Fetch Message');
            $method = $apiData['method'] ?? 'get';
            $url = $apiData['url'] ?? $this->url . 'api/clientwidget_updated/chatroom/';
            $apiKey = $apiData['params']['APIKEY'] ?? $this->apiKey;

            $response = $this->httpClient()->withHeaders([
                'Authorization' => 'Basic ' . $this->getAccessToken(),
                'Cookie' => 'csrftoken=P8PSxHtwF5mI9grg1qhGrYtL55ScXbQoruVlnrdZXj8HWNF0pPcp7MDhpXGt1pDR',
            ])->$method($url . $roomId, [
                'api_key' => $apiKey,
            ]);

            if (! $response->ok()) {
                generateLogs('error', 'Failed to fetch data from Chat360 API.');
                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            generateLogs('error', 'Error in API Calling : ', $e->getMessage());
            return $e->getMessage();
        }
    }

    public function sendMessage(string $type, array $payload, string $phoneNumber)
    {
        try {
            $apiData = $this->getApiConfig('Chat360 Send Message');
            $method = $apiData['method'] ?? 'post';
            $url = $apiData['url'] ?? $this->url . 'api/whatsapp/whatsapp-session-messages';
            $apiKey = $apiData['params']['APIKEY'] ?? $this->apiKey;
            $fromPhone = $apiData['params']['PHONE'] ?? $this->fromPhone;

            $message = [
                'api_key' => $apiKey,
                'from' => $fromPhone,
                'to' => (string)$phoneNumber,
                'recipient_type' => 'individual',
                'type' => $type,
            ];
            switch ($type) {
                case 'text':
                    $message['text'] = [
                        'body' => $payload['body'] ?? '',
                    ];
                    break;

                case 'image':
                    $message['image'] = [
                        'link' => $payload['link'],
                        'caption' => $payload['caption'] ?? '',
                    ];
                    break;

                case 'video':
                    $message['video'] = [
                        'link' => $payload['link'],
                        'caption' => $payload['caption'] ?? '',
                    ];
                    break;

                case 'audio':
                    $message['audio'] = [
                        'link' => $payload['link'],
                    ];
                    break;

                case 'document':
                    $message['document'] = [
                        'link' => $payload['link'],
                        'filename' => $payload['filename'] ?? 'document',
                    ];
                    break;

                case 'location':
                    $message['location'] = [
                        'longitude' => $payload['longitude'],
                        'latitude' => $payload['latitude'],
                        'name' => $payload['name'],
                        'address' => $payload['address'],
                    ];
                    break;

                default:
                    generateLogs('error', "Unsupported message type: {$type}");
                    return "Unsupported message type: {$type}";
            }
            $response = $this->httpClient()->withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
            ])->$method($url, $message);

            if (!in_array($response->status(), [200, 201])) {
                generateLogs('error', 'Failed send data to Chat360.');
                return $response->body();
            }

            return $response->json();
        } catch (Exception $e) {
            generateLogs('error', 'Error in API Calling : ', $e->getMessage());
            return $e->getMessage();
        }
    }

    protected function getApiConfig($type)
    {
        $apiData = ApiConfiguration::whereHas('providers', function ($q) use ($type) {
            $q->where('name', $type);
            $q->where('type', ProviderType::CHATBOT->value);
        })->pluck('config_value', 'config_key');

        $params = [];
        if (!empty($apiData['query_params'])) {
            parse_str($apiData['query_params'], $params);
        }

        return [
            'method' => $apiData['method'],
            'url' => $apiData['url'],
            'params' =>  $params,
            'successCode' => $apiData['success_code'],
        ];
    }

    protected function httpClient()
    {
        $attempt = 0;
        $lastUrl = 'unknown';

        try {
            return Http::timeout($this->timeout)->retry(
                $this->retryAttempts,   // attempts
                $this->retryWait,       // wait in ms
                function ($exception, $request) use (&$attempt, &$lastUrl) {
                    $attempt++;
                    $lastUrl = $request->url();

                    generateLogs(
                        'warning',
                        "Retry attempt {$attempt}/{$this->retryAttempts} for Chat360 API request to {$lastUrl}" .
                            $exception->getMessage()
                    );
                }
            );
        } catch (\Exception $e) {
            generateLogs(
                'error',
                "All {$this->retryAttempts} attempts failed for Chat360 API request to {$lastUrl}" .
                    $e->getMessage()
            );
            throw $e;
        }
    }
}
