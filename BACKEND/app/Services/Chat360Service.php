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



    public function getChatHistory($accessCode,$roomId=NULL)
    {
        $response = Http::get($this->url.'api/clientwidget_updated/listing/third_party_fetch?api_key='.$accessCode.'&room_status=active');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    
}
