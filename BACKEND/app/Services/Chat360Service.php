<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $response = Http::post($this->url.'api/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getAccessToken()
    {
        $loginResponse = $this->login();

        return $loginResponse['access'] ?? null;
    }

    public function getAuthorizationCode()
    {
        $response = Http::withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->get($this->url.'api/third-party-service/auth/verify');

        if ($response->successful()) {
            return $response->json()['authorization_code'] ?? null;
        }

        return null;
    }

    public function getChatHistoryUrl()
    {
        $authCode = $this->getAuthorizationCode();

        if (!$authCode) {
            return null;
        }
        return $this->url."auth/login/sso?authorization_code={$authCode}&next=https%3A%2F%2Fapp.chat360.io%2Fchats%2F{$this->roomId}";
    }

    
}
