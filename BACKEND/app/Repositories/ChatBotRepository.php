<?php

namespace App\Repositories;

use App\Events\CustomerRequest;
use App\Events\NotifyCustomer;
use App\Events\ReceiveChatMessage;
use App\Events\CustomChatbotMessage;
use App\Events\EndConversationCustomer;
use App\Enums\ChatbotMessageStatus;
use App\Http\Response\CustomApiResponse;
use App\Http\Resources\ChatbotResource;
use App\Models\Chatbot;
use App\Models\ChatbotMessage;
use App\Models\ChatbotMessageAttachment;
use App\Models\ChatbotMessageRequest;
use App\Models\Contact;
use App\Services\ChatBotService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ChatBotRepository
{
    private $apiResponse, $chatbotService;

    public function __construct(CustomApiResponse $customApiResponse, ChatBotService $chatbotService)
    {
        $this->apiResponse = $customApiResponse;
        $this->chatbotService = $chatbotService;
    }

    public function list(Request $request)
    {
        try {
            $filters = [
                'search' => 'searchTerm',
                'question' => 'question',
                'answer' => 'answer',
                'keywords' => 'keywords',
                'status' => 'status',
            ];

            $inputs = [];
            foreach ($filters as $key => $variable) {
                $inputs[$variable] = $request->input($key, '');
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $perPage = (int)$perPage;

            $query = Chatbot::query();

            if (!empty($inputs['searchTerm'])) {
                $query->where(function ($q) use ($inputs) {
                    $searchTerm = $inputs['searchTerm'];
                    $q->where('question', 'like', "%{$searchTerm}%")
                        ->orWhere('answer', 'like', "%{$searchTerm}%")
                        ->orWhereJsonContains('keywords', $searchTerm);
                });
            }

            foreach ($filters as $key => $variable) {
                if (!empty($inputs[$variable]) && $variable !== 'searchTerm') {
                    if ($variable === 'status') {
                        $status = filter_var($inputs[$variable], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if (!is_null($status)) {
                            $query->where($variable, $status);
                        }
                    } elseif ($variable === 'keywords') {
                        if (!is_null($inputs['keywords'])) {
                            $query->whereJsonContains('keywords', $inputs['keywords']);
                        }
                    } else {
                        $query->where($variable, 'like', "%{$inputs[$variable]}%");
                    }
                }
            }
            $chatbots = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $chatbots,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Chatboats Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(Request $request)
    {
        try {
            // $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // $request->merge(['status' => $status]);
            $chatbot = Chatbot::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
                'keywords' => $request->keywords ?? [],
            ]);

            $data = new ChatbotResource($chatbot);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.chatbot_create_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $chatbot = Chatbot::findOrFail($id);

            $chatbot->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $status,
                'keywords' => $request->keywords ?? [],
            ]);

            $data = new ChatbotResource($chatbot);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.chatbot_update_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $chatbot = Chatbot::findOrFail($id);

            $chatbot->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $id,
                trans('message.chatbot_delete_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Chatboat: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getQuestions(Request $request)
    {
        try {
            $payload = extractPayload($request->bearerToken());
            $apiPayload = [
                'userid' => $payload['userid'],
                'uin' => $payload['uin'],
                'lang' => $request->lang,
            ];
            $questions = $this->chatbotService->getQuestions($apiPayload);

            if (isset($questions['status_code']) && $questions['status_code'] != 200) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    '',
                    $questions['message'] ?? 'Something went wrong',
                    $questions['status_code']
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $questions,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing of custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function askQuestion(Request $request)
    {
        try {
            $apiPayload = [
                'session_id' => $request->session_id,
                'lang' => $request->lang,
                'query' => $request->question,
            ];
            $askQuestion = $this->chatbotService->ask($apiPayload);

            if (isset($askQuestion['status_code']) && $askQuestion['status_code'] != 200) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    '',
                    $askQuestion['message'] ?? 'Something went wrong',
                    $askQuestion['status_code']
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $askQuestion,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing of custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function connectWithAgent(Request $request)
    {
        $payload = extractPayload($request->bearerToken());
        try {
            $msgRequest = ChatbotMessageRequest::create([
                'chat_id' => (string) Str::uuid(),
                'customer_id' => $payload['userid'] ?? null,
            ]);

            $boradCastData = [
                'customer_id' => $payload['userid'],
                'chat_id' => $msgRequest->chat_id,
                'chat_request_id' => encryptString($msgRequest->id),
            ];
            broadcast(new CustomerRequest($boradCastData, $payload['domain']));
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating webhook for Custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function notifyCustomer(Request $request)
    {
        try {
            $response = null;
            $user = auth()->user();

            DB::transaction(function () use ($request,  &$response, $user) {
                $requestId = decryptString($request->chat_request_id);
               
                $existingRequest = ChatbotMessageRequest::where('id', $requestId)
                    ->where('chat_id', $request->chat_id)
                    ->where('customer_id', $request->customer_id)
                    ->where('status', ChatbotMessageStatus::ACTIVE)
                    ->whereNull('agent_id')
                    ->lockForUpdate()
                    ->first();

                if (!$existingRequest) {
                    $response = response()->json([
                        'message' => 'Customer already connected with an agent.'
                    ], ResponseHTTP::HTTP_CONFLICT);
                    return;
                }

                $existingRequest->update([
                    'agent_id' => $user?->id,
                    'updated_at' => now(),
                ]);
            
                // Fetch chat history
                $chatHistory = $this->chatbotService->fetchHistory($request);

                // If fetchHistory returns an error code
                if (isset($chatHistory['status_code']) && $chatHistory['status_code'] != 200) {
                    $response = $this->apiResponse->getResponseStructure(
                        trans('message.error'),
                        '',
                        $chatHistory['message'] ?? 'Something went wrong',
                        $chatHistory['status_code']
                    );
                    return;
                }

                if (!empty($chatHistory['history'])) {
                    foreach ($chatHistory['history'] as $item) {
                        $messagesToInsert[] = [
                            'chatbot_message_request_id' => $requestId,
                            'session_id' => $item['session_id'] ?? null,
                            'sender' => $item['role'],
                            'text' => $item['message'] ?? $item['content'],
                            'external_message_time' => $item['timestamp'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    ChatbotMessage::insert($messagesToInsert);

                    // $insertedMessages = ChatbotMessage::where('chatbot_message_request_id', $requestId)
                    // ->orderBy('created_at', 'asc')
                    // ->get();
                }

                // if ($insertedMessages) {
                    $broadcastData = [
                        'agent_id' => $user?->id,
                        'chat_request_id' => $request->chat_request_id,
                        'message'  => "You are now connected with agent " . ($user?->username ?? 'Agent'),
                    ];
                    broadcast(new NotifyCustomer($broadcastData, $user?->domain, $request->customer_id));

                    $broadcastData = [
                        'customer_id' => $request->customer_id,
                        'agent_id'    => $existingRequest->agent_id ?? null,
                        'chat_request_id' => $request->chat_request_id,
                    ];
                    broadcast(new CustomerRequest($broadcastData, $user?->domain));
                // }
            });

            if ($response) {
                return $response;
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                '',
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            dd($ex->getMessage());
            generateLogs('error', 'Error in creating webhook for Custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function storeMessage(Request $request)
    {
        try {
            $payload = extractPayload($request->bearerToken());
            if ($request->sender == 'agent') {
                $customerId = $request->receiver_id;
                $agentId = auth()->user()->id;
            } else {
                $customerId = $payload['userid'];
                $agentId = $request->receiver_id;
            }
            $files = uploadAttachment($request, "attachment", "attachment");

            $message = ChatbotMessage::Create([
                'chatbot_message_request_id' => decryptString($request->chat_request_id),
                'agent_id' => $agentId,
                'customer_id' => $customerId,
                'text' => $request->text,
                'sender' => $request->sender,
                'status' => ChatbotMessageStatus::ACTIVE,
                'external_message_time' => Carbon::now()
            ]);

            if ($message) {
                if ($files) {
                    foreach ($files as $file) {
                        $fileData[] = [
                            'message_id' => $message->id,
                            'attachments' => $file['fileName'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    ChatbotMessageAttachment::insert($fileData);
                }

                if ($request->sender == 'agent') {
                    $boradCastData = [
                        'customer_id' => $customerId,
                        'msg' => $request->text,
                    ];
                    broadcast(new ReceiveChatMessage($boradCastData, auth()->user()->domain));
                } else {
                    $boradCastData = [
                        'customer_id' => $customerId,
                        'agent_id' => $agentId,
                        'msg' => $request->text,
                    ];
                    broadcast(new CustomChatbotMessage($boradCastData, auth()->user()->domain));
                }
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $message, trans('message.message_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in storing Message: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function endConversationCustomer(Request $request)
    {
        try {
            ChatbotMessageRequest::where('agent_id', auth()->user()->id)
                ->where('customer_id', $request->customer_id)
                ->update([
                    'status' => ChatbotMessageStatus::DISCONNECTED,
                ]);

            $boradCastData = [
                //  'agent_id' => auth()->user()->id,
                'message' => "Thank you for connecting with us",
            ];
            broadcast(new EndConversationCustomer($boradCastData, auth()->user()->domain, $request->customer_id));
            return $this->apiResponse->getResponseStructure(trans('message.success'), '', trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating webhook for Custom Chatbot: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getCustomerIds()
    {
        try {
            // $messages = ChatbotMessage::whereNotNull("customer_id")
            //     ->where('agent_id', auth()->user()->id)
            //     ->orderBy("id", "desc")
            //     ->get()
            //     ->groupBy('customer_id')
            //     ->map(function ($group) {
            //         $contact = Contact::where('id', $group->first()->customer_id)
            //             ->whereNull('deleted_at')
            //             ->first();

            //         return [
            //             'customer_id' => (string) $group->first()->customer_id,
            //             'name'        => $contact
            //                 ? trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''))
            //                 : 'Unknown',
            //         ];
            //     })
            //     ->values();

            $customers = ChatbotMessageRequest::where('agent_id', auth()->id())
                ->whereNotNull('customer_id')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($req) {
                    $contact = Contact::where('id', $req->customer_id)
                        ->whereNull('deleted_at')
                        ->first();

                    return [
                        'customer_id' => (string) $req->customer_id,
                        'name'        => $contact
                            ? trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''))
                            : 'Unknown',
                    ];
                })
                ->values();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $customers,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function getCustomerMessages($request)
    {
        try {
            // $messages  = ChatbotMessage::where('customer_id', $request->id)
            //     ->where('agent_id', auth()->user()->id)
            //     ->get();

            $messages = ChatbotMessage::with('request')
                ->whereHas('request', function ($query) use ($request) {
                    $query->where('customer_id', $request->id)
                        ->where('agent_id', auth()->id());
                })
                ->get();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $messages, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Message Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
