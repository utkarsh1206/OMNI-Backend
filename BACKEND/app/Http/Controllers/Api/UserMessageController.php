<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\UserMessageRepository;
use App\Http\Requests\UserMessageRequest;
use App\Http\Requests\ChatBotRoomId;
use App\Http\Requests\GetMessagesRequest;
use App\Models\Detail;

class UserMessageController extends Controller
{
    private $messageRepository;

    public function __construct(UserMessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @OA\Post(
     *     path="/assign-chat-to-agent",
     *     summary="Assign chat to agent and store chat history",
     *     operationId="assignChatToAgent",
     *     tags={"User Messages"},
     *     security={{
     *         "bearerAuth": {}
     *     }},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id"},
     *             @OA\Property(property="room_id", type="string", example="abc123"),
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Chat messages processed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input or missing parameters."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     )
     * )
     */
    public function assignChatToAgent(ChatBotRoomId $request)
    {
        return $this->messageRepository->assignChatToAgent($request);
    }

    /**
     * @OA\Get(
     *     path="/api/get-user-phones",
     *     summary="Get phones of customer",
     *     description="Fetches all unique customer phone numbers for user messages.",
     *     operationId="getUserPhones",
     *     tags={"User Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of phone numbers",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="string", example="919725958612")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function getUserPhones(Request $request)
    {
        return $this->messageRepository->getUserPhones($request);
    }

    /**
     * @OA\Get(
     *     path="/api/get-messages",
     *     summary="Get messages by phone",
     *     description="Fetches all messages for a given customer phone number (for the authenticated user).",
     *     operationId="getMessages",
     *     tags={"User Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=true,
     *         description="Customer phone number to filter messages",
     *         @OA\Schema(type="string", example="919725958612")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of messages",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=130),
     *                     @OA\Property(property="user_id", type="integer", example=4),
     *                     @OA\Property(property="text", type="string", example="Hello, how can I help you?"),
     *                     @OA\Property(property="is_read", type="string", example="read"),
     *                     @OA\Property(property="room_id", type="string", example="26539560-b38f-43ad-a5de-8278fcb6cb85"),
     *                     @OA\Property(property="sender", type="string", example="bot"),
     *                     @OA\Property(property="external_message_time", type="string", format="date-time", example="2025-08-13 13:18:50"),
     *                     @OA\Property(property="node_type", type="string", example="MESSAGE"),
     *                     @OA\Property(property="session_id", type="string", example="3ace155d-1acd-426e-af02-8ff95be8f1ac"),
     *                     @OA\Property(property="first_name", type="string", example="System"),
     *                     @OA\Property(property="last_name", type="string", example="Message"),
     *                     @OA\Property(property="external_user_phone", type="string", example="919725958612"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-13T13:33:04.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-13T13:33:04.000000Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Phone parameter is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function getMessages(GetMessagesRequest $request)
    {
        return $this->messageRepository->getMessages($request);
    }

    /**
     * @OA\Post(
     *     path="/api/send-message",
     *     summary="Store a new message",
     *     description="Creates a new message for a specific user. Supports file attachment.",
     *     operationId="storeMessage",
     *     tags={"User Messages"},
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Message data to be stored",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"text"},
     *                 @OA\Property(property="text", type="string", example="This is a message"),
     *                 @OA\Property(
     *                     property="attachment",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional file to be attached"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Message created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=12),
     *                 @OA\Property(property="text", type="string", example="This is a message"),
     *                 @OA\Property(property="attachment", type="string", nullable=true, example="uploads/messages/file.jpg"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-03T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-03T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function storeMessage(UserMessageRequest $request)
    {
        return $this->messageRepository->storeMessage($request);
    }

    public function fetchChatResponse(Request $request, Detail $detail)
    {
        return $this->messageRepository->fetchChatResponse($request, $detail);
    }
}
