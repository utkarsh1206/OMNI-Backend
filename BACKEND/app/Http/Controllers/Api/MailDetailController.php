<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailDetailIndexRequest;
use App\Http\Requests\MailDetailRequest;
use App\Repositories\MailDetailRepository;


class MailDetailController extends Controller
{

    private $mailDetailRepository;

    public function __construct(MailDetailRepository $mailDetailRepository)
    {
        $this->mailDetailRepository = $mailDetailRepository;
    }
    /**
     * @OA\Get(
     *     path="/mail-details",
     *     summary="Get Mail Details by Support Ticket ID",
     *     description="Fetches the mail details associated with a specific support ticket ID.",
     *     operationId="getMailDetailsBySupportTicketId",
     *     tags={"MailDetails"},
     *     @OA\Parameter(
     *         name="supportticket_id",
     *         in="query",
     *         required=true,
     *         description="The ID of the support ticket to fetch the mail details for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved mail details.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="supportticket_id", type="integer"),
     *                     @OA\Property(property="date", type="string", format="date-time"),
     *                     @OA\Property(property="user_id", type="integer"),
     *                     @OA\Property(property="type", type="string"),
     *                     @OA\Property(property="priority", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="to", type="string"),
     *                     @OA\Property(property="cc", type="string"),
     *                     @OA\Property(property="bcc", type="string"),
     *                     @OA\Property(property="body", type="string"),
     *                     @OA\Property(property="attachment", type="array", items=@OA\Items(type="string"))
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mail details not found for the specified support ticket ID."
     *     )
     * )
     */
    public function index(MailDetailIndexRequest $request)
    {
        return $this->mailDetailRepository->index($request);
    }



    /**
 * @OA\Post(
 *     path="/mail-details",
 *     summary="Send Mail and Store Mail Detail",
 *     description="Sends an email with attachments and stores mail details for a support ticket.",
 *     operationId="storeMailDetail",
 *     tags={"MailDetails"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Mail details to be sent and stored",
 *         @OA\JsonContent(
 *             required={"supportticket_id", "user_id", "to", "body"},
 *             type="object",
 *             properties={
 *                 @OA\Property(property="supportticket_id", type="integer", description="ID of the support ticket"),
 *                 @OA\Property(property="user_id", type="integer", description="User ID of the sender"),
 *                 @OA\Property(property="to", type="string", description="Recipient's email address"),
 *                 @OA\Property(property="cc", type="string", description="CC email addresses"),
 *                 @OA\Property(property="bcc", type="string", description="BCC email addresses"),
 *                 @OA\Property(property="body", type="string", description="Body content of the email"),
 *                 @OA\Property(property="default_type", type="string", description="The default type of the email"),
 *                 @OA\Property(property="default_priority", type="string", description="The default priority of the email"),
 *                 @OA\Property(property="default_status", type="string", description="The default status of the email"),
 *                 @OA\Property(
 *                     property="attachment",
 *                     type="array",
 *                     description="The email attachments",
 *                     @OA\Items(
 *                         type="file"
 *                     )
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Mail sent successfully and mail details stored.",
 *         @OA\JsonContent(
 *             type="object",
 *             properties={
 *                 @OA\Property(property="message", type="string", example="Mail sent successfully."),
 *                 @OA\Property(
 *                     property="data",
 *                     type="object"
 *                 )
 *             }
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad request, missing or invalid parameters."
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to send email, internal server error."
 *     )
 * )
 */
    public function store(MailDetailRequest $request)
    {
        return $this->mailDetailRepository->store($request);
    }
}
