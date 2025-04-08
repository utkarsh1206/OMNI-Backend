<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailRequest;
use App\Repositories\EmailRepository;

class EmailController extends Controller
{
    private $emailRepository;

    public function __construct(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/send-email",
     *     summary="Send an email",
     *     description="Sends an email to a specified recipient using SMTP settings from the database.",
     *     operationId="sendEmail",
     *     tags={"Email"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"configure_id", "subject", "content", "to"},
     *             @OA\Property(property="configure_id", type="integer", example=1, description="ID of the email configuration"),
     *             @OA\Property(property="subject", type="string", example="Important Update", description="Subject of the email"),
     *             @OA\Property(property="content", type="string", example="This is the body of the email.", description="Content of the email"),
     *             @OA\Property(property="to", type="string", format="email", example="user@example.com", description="Recipient's email address")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Email sent successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send email",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Failed to send email"),
     *             @OA\Property(property="error", type="string", example="SMTP authentication failed")
     *         )
     *     )
     * )
     */
    public function sendEmail(EmailRequest $request)
    {
        $validated = $request->validated();
        return $this->emailRepository->index($validated);
    }
}
