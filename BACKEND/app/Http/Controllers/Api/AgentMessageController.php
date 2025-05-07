<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AgentMessageRepository;
use App\Http\Requests\MessageRequest;

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
     *     tags={"Agents"},
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
     *                     @OA\Property(property="status", type="string", example="available")
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
     *     tags={"Agent Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="agent_id",
     *         in="path",
     *         required=true,
     *         description="ID of the agent to fetch messages with",
     *         @OA\Schema(type="integer", example=42)
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
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="receiver_id", type="integer", example=42),
     *                     @OA\Property(property="text", type="string", example="Hello Agent!"),
     *                     @OA\Property(property="is_read", type="string", example="unread"),
     *                     @OA\Property(property="type", type="string", example="agent_chat"),
     *                     @OA\Property(property="attachment", type="string", nullable=true, example=null),
     *                     @OA\Property(property="sender", type="string", example="agent"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-07T08:15:00Z")
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
    public function getMessages($agentId)
    {
        return $this->messageRepository->getMessages($agentId);
    }

    /**
     * @OA\Post(
     *     path="/api/send-agent-messages",
     *     summary="Store a new message",
     *     description="Creates a new message for a specific user. Supports file attachment.",
     *     operationId="storeMessage",
     *     tags={"Messages"},
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
    public function storeMessage(MessageRequest $request)
    {
        return $this->messageRepository->storeMessage($request);
    }
}
