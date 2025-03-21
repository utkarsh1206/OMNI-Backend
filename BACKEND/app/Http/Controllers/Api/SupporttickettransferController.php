<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SupportTicketTransferRepository;
use Illuminate\Http\Request;

class SupportTicketTransferController extends Controller
{
    private $supportTicketTransferRepository;

    public function __construct(SupportTicketTransferRepository $supportTicketTransferRepository)
    {
        $this->supportTicketTransferRepository = $supportTicketTransferRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/transfer",
     *     summary="Transfer a Support Ticket",
     *     description="Transfer a support ticket to a new user and create a notification for the transfer.",
     *     tags={"Support Ticket"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"supportticket_id", "user_id", "comment"},
     *             @OA\Property(property="supportticket_id", type="integer", description="The ID of the support ticket to transfer"),
     *             @OA\Property(property="user_id", type="integer", description="The ID of the user to whom the ticket is being assigned"),
     *             @OA\Property(property="comment", type="string", description="The comment to be added for the transfer notification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ticket transferred successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input or missing required fields")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing the request")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        return $this->supportTicketTransferRepository->store($request);
    }
}
