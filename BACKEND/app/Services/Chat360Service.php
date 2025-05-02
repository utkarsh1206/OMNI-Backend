<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class Chat360Service
{
    protected $email;
    protected $password;
    protected $url;
    protected $apiKey;
    protected $roomId;

    public function __construct()
    {
        $this->email =  config('omni.CHAT_360_EMAIL');
        $this->password =  config('omni.CHAT_360_PASSWORD');
        $this->url =  config('omni.CHAT_360_URL');
        $this->apiKey = config('omni.CHAT_360_API_KEY');
        $this->roomId = config('omni.CHAT_360_ROOM_ID');
    }

    public function login()
    {
        try {
            $response = Http::post($this->url . 'api/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->status() == 200) {
                return $response->json();
            }

            return null;
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

    public function getChatHistory()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.$this->getAccessToken(),
                'Cookie' => 'csrftoken=P8PSxHtwF5mI9grg1qhGrYtL55ScXbQoruVlnrdZXj8HWNF0pPcp7MDhpXGt1pDR',
            ])->get($this->url. 'api/clientwidget_updated/chatroom/'.$this->roomId, [
                'api_key' => $this->apiKey,
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

    public function sendMessage(string $type, array $payload)
    {
        try {

            $message = [
                'api_key' => $this->apiKey,
                'from' => 'dummyData',       // Replace with dynamic value if needed
                'to' => 'dummyData',         // Replace with dynamic value if needed
                'recipient_type' => 'individual',
                'type' => $type,
            ];
    
            // Attach message-specific payload
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
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->getAccessToken(),
            ])->post($this->url. 'api/whatsapp/whatsapp-session-messages', $message);

            if (! $response->ok()) {
                generateLogs('error', 'Failed send data to Chat360.');
                return $response->body();
            }

            return $response->json();
        } catch (Exception $e) {
            generateLogs('error', 'Error in API Calling : ', $e->getMessage());
            return $e->getMessage();
        }
    }
}
