<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappBroadcastController extends Controller
{
   public function sendBulk(Request $request)
    {
        generateLogs("info", "WA BULK Called " . json_encode($request->all()));
        $phones = $request->input('phones', []);

        if (empty($phones)) {
            generateLogs("info", "WA Bulk, no phone provided");
            return response()->json(['message' => 'No phone numbers provided'], 400);
        }

        $taskBody = array_map(function ($phone) {
            return [
                "receiver_number" => (string) $phone,
                "template_data" => [
                    "param_data" => (object)[]
                ]
            ];
        }, $phones);

        generateLogs("info", "Task Body " . json_encode($taskBody));
        $payload = [
            "task_name" => "whatsapp_push_notification",
            "extra" => "",
            "template_id" => "897e4449-d8f4-41a5-b7c4-a8265ae527c9",
            "client_number" => "919712900567",
            "task_body" => $taskBody
        ];

        generateLogs("info", "Payload is " . json_encode($payload));
        try {
            generateLogs("info", "Sending api to chat360");
            $response = Http::withHeaders([
                'Authorization' => 'Api-Key eGsKut2S.nZMML3Ipj7kgZltmEV22BcGR9t15M4sU',
                'Content-Type' => 'application/json',
            ])->post('https://app.chat360.io/service/v1/task_bulk', $payload);

            generateLogs("info", "Request Sent");
            return response()->json($response->json(), $response->status());

        } catch (\Exception $e) {
            generateLogs("error", 'Broadcast Error: ' , $e->getMessage());
            return response()->json(['message' => 'Server error while sending broadcast'], 500);
        }
    }
}