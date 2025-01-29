<?php

namespace App\Services;

use App\Models\UserSmsGateway;
use Twilio\Rest\Client as TwilioClient;
use App\Models\SmsProviderConfiguration;
use GuzzleHttp\Client as GuzzleClient;
use Exception;

class SmsService
{
    /**
     * Send SMS based on the selected provider for the given user.
     *
     * @param int $userId
     * @param string $message
     * @param string $toPhone
     * @return mixed
     * @throws Exception
     */
    public function sendSMS(int $userId, string $message, string $toPhone)
    {
        $userGateway = UserSmsGateway::where('user_id', $userId)->first();
        if (!$userGateway) {
            throw new Exception('No SMS gateway selected for this user.');
        }

        $providerId = $userGateway->provider_id;
        // Based on the provider, call the respective API
        switch ($userGateway->provider->name) {
            case 'twilio':
                $configArr = SmsProviderConfiguration::where("provider_id", $providerId)->pluck("config_value","config_key");
                return $this->sendWithTwilio($message, $toPhone, $configArr);

                case 'custom':
                    $configArr = [];
                  
                    $configurations = SmsProviderConfiguration::where("provider_id", $providerId)->pluck("config_value","config_key");
                    if($configurations->count())
                    {
                        $configArr =  $configurations->toArray();
                    }
                    return $this->sendWithCustomProvider($message, $toPhone, $configArr);
            default:
                throw new Exception('Unsupported SMS provider selected.');
        }
    }

    /**
     * Send SMS using Twilio.
     *
     * @param string $message
     * @param string $toPhone
     * @return mixed
     */
    private function sendWithTwilio(string $message, string $toPhone,array $configArr)
    {
        // $client = new TwilioClient($sid, $token);
        // // Send the SMS
        // return $client->messages->create(
        //     $toPhone, // To phone number
        //     [
        //         'from' => $phoneNumber,
        //         'body' => $message,
        //     ]
        // );
    }

    private function sendWithCustomProvider(string $message, string $toPhone, array $configArr)
    {
       
        $client = new GuzzleClient();
        $apiName = $configArr['api_name'] ?? '';
        $queryParams = $configArr['query_params'] ?? '';
        $phoneNumber = $configArr['phone_number'] ?? '';
        $apiUrl = $configArr['api_url'] ?? '';
        $method = $configArr['method'] ?? 'POST';  // Default to POST if not set
        try {
            $queryParams = str_replace("[PHONE_NUMBER]",$toPhone, $queryParams);
            $urlWithParams = $apiUrl.$apiName.$queryParams;
            $response = $client->request($method,$urlWithParams);
            $responseBody = json_decode($response->getBody(), true);
            if (in_array($responseBody,["1702","1703","1704","1705","1706","1707","1708","1709","1710","1025","1715","1032","1028","1051","1052"])) {
                return 'Failed to send message: ' . ($responseBody['error'] ?? 'Unknown error');
            }
            else
            {
                return 'Message sent successfully';
            }
          
        } catch (Exception $e) {
            return 'Error calling custom provider: ' . $e->getMessage();
        }
}




  
   
}
