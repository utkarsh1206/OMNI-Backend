<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatbot;
use App\Http\Resources\ChatbotResource;
use App\Repositories\ChatBotRepository;
use App\Http\Requests\ChatbotRequest;
use App\Http\Requests\ChatbotAskQuestionRequest;
use App\Http\Response\CustomApiResponse;
use Exception;

class ChatBotController extends Controller
{

    protected $chatBotRepository;

    private $apiResponse;

    public function __construct(ChatBotRepository $chatBotRepository, CustomApiResponse $customApiResponse)
    {
        $this->chatBotRepository = $chatBotRepository;
        $this->apiResponse = $customApiResponse;
    }

    /**
     * @OA\Get(
     *      path="/api/chatbots",
     *      tags={"Chatbots"},
     *      summary="Get a list of all chatbots",
     *      description="Retrieve a paginated list of all chatbots.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="Page number",
     *          @OA\Schema(type="integer", default=1)
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          required=false,
     *          description="Number of items per page",
     *          @OA\Schema(type="integer", default=10)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Data has been fetched successfully!"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="question", type="string", example="How can I track my order?"),
     *                      @OA\Property(property="answer", type="string", example="You can track your order using the tracking number."),
     *                      @OA\Property(property="status", type="boolean", example=true),
     *                      @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-07T12:23:44.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-07T12:23:44.000000Z")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="No chatbots found"
     *      )
     * )
     */

    public function index(Request $request)
    {
        return $this->chatBotRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/chatbots",
     *     tags={"Chatbots"},
     *     summary="Create a new chatbot",
     *     @OA\Parameter(
     *         name="question",
     *         in="query",
     *         required=true,
     *         description="The question to be asked by the chatbot",
     *         @OA\Schema(type="string", example="How can I return my product?")
     *     ),
     *     @OA\Parameter(
     *         name="answer",
     *         in="query",
     *         required=true,
     *         description="The answer the chatbot will provide",
     *         @OA\Schema(type="string", example="You can return your product within 30 days.")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=true,
     *         description="Status of the chatbot (active/inactive)",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="Keywords",
     *         in="query",
     *         required=false,
     *         description="Keywords",
     *         @OA\Schema(
     *           @OA\Property(
     *               property="Keywords[]",
     *               description="Keywords",
     *               type="array",
     *               collectionFormat="multi",
     *              @OA\Items(type="string", format="id")
     *           ))
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Chatbot created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Chatbot created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, invalid token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized, invalid token")
     *         )
     *     )
     * )
     */

    public function store(ChatbotRequest $request)
    {
        return $this->chatBotRepository->create($request);
    }

    public function show($chatbot)
    {
        try {
            $data = Chatbot::findOrFail($chatbot);
            return new ChatbotResource($data);
        } catch (Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/chatbots/{chatbot}",
     *      tags={"Chatbots"},
     *      summary="Update an existing chatbot",
     *      description="Update the details of an existing chatbot.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="chatbot",
     *          in="path",
     *          required=true,
     *          description="ID of the chatbot",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Updated chatbot data",
     *          @OA\JsonContent(
     *              type="object",
     *              required={"question", "answer", "status"},
     *              @OA\Property(property="question", type="string", example="Can I change my order?"),
     *              @OA\Property(property="answer", type="string", example="Yes, you can change your order within 24 hours."),
     *              @OA\Property(property="status", type="boolean", example=true)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Chatbot updated successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Chatbot updated successfully"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid request data"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chatbot not found"
     *      )
     * )
     */

    public function update(Request $request, $id)
    {
        return $this->chatBotRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *      path="/api/chatbots/{chatbot}",
     *      tags={"Chatbots"},
     *      summary="Delete a chatbot",
     *      description="Delete a specific chatbot by ID.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="chatbot",
     *          in="path",
     *          required=true,
     *          description="ID of the chatbot",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Chatbot deleted successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Chatbot deleted successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized, invalid token"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chatbot not found"
     *      )
     * )
     */

    public function destroy($id)
    {
        return $this->chatBotRepository->delete($id);
    }

    public function askQuestion(ChatbotAskQuestionRequest $request)
    {
        return $this->chatBotRepository->askQuestion($request);
    }
}
