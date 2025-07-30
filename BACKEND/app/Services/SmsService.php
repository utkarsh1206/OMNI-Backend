<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;
use App\Models\ApiConfiguration;
use App\Models\SmsProvider;
use GuzzleHttp\Client as GuzzleClient;
use Exception;
use App\Models\SmsProviderLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class SmsService implements ShouldQueue
{
    /**
     * Send SMS based on the selected provider for the given user.
     *
     * @param int $userId
     * @param string $templateName
     * @param string $message
     * @param string $toPhone
     * @return mixed
     * @throws Exception
     */
    public function sendSMS(int $userId, string $templateName, string $message, string $toPhone)
    {
        $userGateway = SmsProvider::where(['status' => "active", "type" => 1])->orderBy("id", "desc")->first();

        // if (!$userGateway) {
        //     throw new Exception('No SMS gateway selected for this user.');
        // }

        if ($userGateway && $userGateway->id) {
            $providerId = $userGateway->id;
            // Based on the provider, call the respective API

            $configArr = [];

            $configurations = ApiConfiguration::where(["provider_id" => $providerId, "api_config_type_id" => 1])->pluck("config_value", "config_key");
            if ($configurations->count()) {
                $configArr =  $configurations->toArray();
            }
            return $this->sendWithCustomProvider($templateName, $message, $toPhone, $configArr, $providerId);
        }
    }

    /**
     * Send SMS using Twilio.
     *
     * @param string $message
     * @param string $toPhone
     * @return mixed
     */
    private function sendWithTwilio(string $message, string $toPhone, array $configArr)
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

    private function sendWithCustomProvider(string $templateName, string $message, string $toPhone, array $configArr, $providerId)
    {
        $client = new GuzzleClient();
        // $apiName = $configArr['api_name'] ?? '';
        $queryParams = $configArr['query_params'] ?? '';
        $phoneNumber = $configArr['phone_number'] ?? '';
        $apiUrl = $configArr['url'] ?? '';
        $method = $configArr['method'] ?? 'GET';
        switch ($templateName) {
            case 'Ticket Created':
                $template_id = config('omni.INCIDENT_CREATE');
                break;
            case 'Ticket Resolved':
                $template_id = config('omni.INCIDENT_RESOLUTION');
                break;
            case 'OTP Send':
                $template_id = config('omni.PROVISIONING_OTP');
                break;
        }
        try {
            $queryParams = str_replace("[PHONE_NUMBER]", $toPhone, $queryParams);
            // $queryParams = str_replace("[TEMPLATE_ID]", $template_id, $queryParams);
            $queryParams .= "&tempid=" . $template_id;
            $queryParams .= "&message=" . rawurldecode($message);
            $urlWithParams = $apiUrl . $queryParams;
            $response = $client->request($method, $urlWithParams);
            $responseBody = json_decode($response->getBody(), true);
            if (in_array($responseBody, ["1702", "1703", "1704", "1705", "1706", "1707", "1708", "1709", "1710", "1025", "1715", "1032", "1028", "1051", "1052"])) {
                $smsLogs = new SmsProviderLog;
                $smsLogs->fill([
                    "provider_id" => $providerId,
                    "message_id" => 0,
                    "status" => "failed",
                    "error_message" => "Failed to send message",
                ]);
                $smsLogs->save();
                return 'Failed to send message: ' . ($responseBody['error'] ?? 'Unknown error');
            } else {
                $smsLogs = new SmsProviderLog;
                $smsLogs->fill([
                    "provider_id" => $providerId,
                    "message_id" => 0,
                    "status" => "sent",
                ]);
                $smsLogs->save();
                return 'Message sent successfully';
            }
        } catch (Exception $e) {
            return 'Error calling custom provider: ' . $e->getMessage();
        }
    }
}
