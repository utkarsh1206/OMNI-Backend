<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AgentMessageRepository;
use App\Http\Requests\AgentMessageRequest;
use App\Http\Requests\AgentMessageReadRequest;

class AgentMessageController extends Controller
{
    private $messageRepository;

    public function __construct(AgentMessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/get-agent-status",
     *     summary="Get status of all users",
     *     description="Returns a list of agents along with their availability status based on presence in live_agents table.",
     *     tags={"Agent Message"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="online_status", type="string", example="online"),
     *                     @OA\Property(property="last_message", type="string", example="Hello, I need help."),
     *                     @OA\Property(property="last_message_time", type="string", example="2025-05-07 07:17:04"),
     *                     @OA\Property(property="unread_count", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="data", type="string", example=null)
     *         )
     *     )
     * )
     */
    public function getStatus(Request $request)
    {
        return $this->messageRepository->getStatus($request);
    }

    /**
     * @OA\Get(
     *     path="/api/get-agent-messages/{agent_id}",
     *     summary="Fetch chat messages between current user and given agent",
     *     description="Returns all messages where the current user is either the sender or receiver and the given agent is the other party.",
     *     tags={"Agent Message"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="agent_id",
     *         in="path",
     *         required=true,
     *         description="ID of the agent to fetch messages with",
     *         @OA\Schema(type="integer", example=42)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of messages per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Text to search for within the message content",
     *         @OA\Schema(type="string", example="Hiiii")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=18),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="receiver_id", type="integer", example=2),
     *                         @OA\Property(property="type", type="string", example="agent_chat"),
     *                         @OA\Property(property="text", type="string", example="This is test message with attachment"),
     *                         @OA\Property(property="is_read", type="string", example="unread"),
     *                         @OA\Property(property="room_id", type="integer", nullable=true, example=null),
     *                         @OA\Property(property="sender", type="string", example="agent"),
     *                         @OA\Property(property="external_message_time", type="string", example="2025-05-13 23:38:45"),
     *                         @OA\Property(property="node_type", type="string", nullable=true, example=null),
     *                         @OA\Property(property="session_id", type="string", nullable=true, example=null),
     *                         @OA\Property(property="first_name", type="string", nullable=true, example=null),
     *                         @OA\Property(property="last_name", type="string", nullable=true, example=null),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-13T18:08:45.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-13T18:08:45.000000Z"),
     *                         @OA\Property(property="user_first_name", type="string", example="ha11"),
     *                         @OA\Property(property="user_last_name", type="string", example="Doe1"),
     *                         @OA\Property(
     *                             property="attachment_urls",
     *                             type="array",
     *                             @OA\Items(type="string", example="http://127.0.0.1:8000/storage/attachment/2025_05_13_23_38_45.PNG")
     *                         )
     *                     )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="data", type="string", example=null)
     *         )
     *     )
     * )
     */
    public function getMessages(Request $request, $agentId)
    {
        return $this->messageRepository->getMessages($request, $agentId);
    }

    /**
     * @OA\Post(
     *     path="/api/send-agent-messages",
     *     summary="Store a new message",
     *     description="Creates a new message for a specific user. Supports file attachment.",
     *     tags={"Agent Message"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"agent_id", "text"},
     *                 @OA\Property(
     *                     property="agent_id",
     *                     type="integer",
     *                     description="ID of the recipient agent",
     *                     example=2
     *                 ),
     *                 @OA\Property(
     *                     property="text",
     *                     type="string",
     *                     description="Message text content",
     *                     example="This is a test message"
     *                 ),
     *                 @OA\Property(
     *                     property="attachment[]",
     *                     type="array",
     *                     description="Optional file attachments",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message stored successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Message has been created successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=101),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="receiver_id", type="integer", example=2),
     *                 @OA\Property(property="type", type="string", example="agent_chat"),
     *                 @OA\Property(property="text", type="string", example="This is a test message"),
     *                 @OA\Property(property="is_read", type="string", example="unread"),
     *                 @OA\Property(property="sender", type="string", example="agent"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-13T18:08:45.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-13T18:08:45.000000Z"),
     *                 @OA\Property(
     *                     property="attachment_urls",
     *                     type="array",
     *                     @OA\Items(type="string", example="http://127.0.0.1:8000/storage/attachment/file.png")
     *                 ),
     *                 @OA\Property(property="user_first_name", type="string", example="John"),
     *                 @OA\Property(property="user_last_name", type="string", example="Doe")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="data", type="string", example=null)
     *         )
     *     )
     * )
     */
    public function storeMessage(AgentMessageRequest $request)
    {
        return $this->messageRepository->storeMessage($request);
    }

    /**
     * @OA\Post(
     *     path="/api/last-read-message",
     *     summary="Mark messages as read for a given agent",
     *     description="Updates unread messages where receiver_id matches the agent_id and id is less than or equal to last_message_id.",
     *     tags={"Agent Message"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"agent_id", "last_message_id"},
     *             @OA\Property(property="agent_id", type="integer", example=2, description="The ID of the agent"),
     *             @OA\Property(property="last_message_id", type="integer", example=18, description="The ID of the last message to mark as read")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Messages marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="updated_count", type="integer", example=2),
     *             @OA\Property(
     *                 property="updated_messages",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="receiver_id", type="integer", example=2),
     *                     @OA\Property(property="is_read", type="string", example="unread"),
     *                     @OA\Property(property="message", type="string", example="Hello Agent")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="messages",
     *                 type="object",
     *                 @OA\Property(
     *                     property="agent_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected agent_id is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function lastReadMessage(AgentMessageReadRequest $request)
    {
        return $this->messageRepository->lastReadMessage($request);
    }

    public function storeBulkMessage(Request $request)
    {
        return $this->messageRepository->storeBulkMessage($request);
    }
}
