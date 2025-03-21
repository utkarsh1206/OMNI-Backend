<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SupportTicketMergeRepository;

class SupportTicketMergeController extends Controller
{
    private $supportTicketMergeRepository;

    public function __construct(SupportTicketMergeRepository $supportTicketMergeRepository)
    {
        $this->supportTicketMergeRepository = $supportTicketMergeRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/merge",
     *     summary="Merge Two Support Tickets",
     *     description="Merge one support ticket into another, close the merged ticket, and send a notification.",
     *     tags={"Support Ticket"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"merge_supportticket_id", "supportticket_id"},
     *             @OA\Property(property="merge_supportticket_id", type="integer", description="The ID of the support ticket to be merged and closed"),
     *             @OA\Property(property="supportticket_id", type="integer", description="The ID of the support ticket into which the first ticket is merged")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket successfully merged",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ticket merged successfully"),
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
        return $this->supportTicketMergeRepository->store($request);
    }
}
