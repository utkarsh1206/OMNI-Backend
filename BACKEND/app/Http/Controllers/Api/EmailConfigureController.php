<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailConfigureRequest;
use App\Models\EmailConfigure;
use App\Http\Resources\TenantsResource;
use App\Repositories\EmailConfigureRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class EmailConfigureController extends Controller
{

    private $emailConfigureRepository;

    public function __construct(EmailConfigureRepository $emailConfigureRepository)
    {
        $this->emailConfigureRepository = $emailConfigureRepository;
    }
     /**
     * @OA\Get(
     *     path="/api/email-configure",
     *     summary="Get all email configurations",
     *     description="Fetch email configurations with optional search and pagination.",
     *     tags={"Email Configuration"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for filtering email configurations",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Results per page for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of email configurations",
     *         @OA\JsonContent(
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching email configurations")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->emailConfigureRepository->list($request);
    }


    /**
     * @OA\Post(
     *     path="/api/email-configure",
     *     summary="Create a new email configuration",
     *     description="Create a new email configuration with the provided data.",
     *     tags={"Email Configuration"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "smtp_host", "smtp_port", "smtp_user", "smtp_password"},
     *             @OA\Property(property="type", type="string", example="SMTP"),
     *             @OA\Property(property="smtp_host", type="string", example="smtp.example.com"),
     *             @OA\Property(property="smtp_port", type="integer", example=587),
     *             @OA\Property(property="smtp_user", type="string", example="user@example.com"),
     *             @OA\Property(property="smtp_password", type="string", example="password123"),
     *             @OA\Property(property="default_type", type="string", example="default"),
     *             @OA\Property(property="default_status", type="string", example="active"),
     *             @OA\Property(property="imap_host", type="string", example="imap.example.com"),
     *             @OA\Property(property="imap_port", type="integer", example=993),
     *             @OA\Property(property="imap_user", type="string", example="user@imap.com"),
     *             @OA\Property(property="imap_password", type="string", example="imapPassword123"),
     *             @OA\Property(property="sender_name", type="string", example="Sender Name"),
     *             @OA\Property(property="sender_email", type="string", example="sender@example.com"),
     *             @OA\Property(property="assign_method", type="string", example="auto"),
     *             @OA\Property(property="signature", type="string", example="Best regards, Team")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Email configuration created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration created successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating email configuration")
     *         )
     *     )
     * )
     */
    public function store(EmailConfigureRequest $request)
    {
        return $this->emailConfigureRepository->store($request);
    }

     /**
     * @OA\Get(
     *     path="/api/email-configure/{id}",
     *     summary="Get an email configuration by ID",
     *     description="Fetch a specific email configuration by its ID.",
     *     tags={"Email Configuration"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the email configuration",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email configuration details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching email configuration")
     *         )
     *     )
     * )
     */
    public function show(EmailConfigure $email_configure)
    {
        return $this->emailConfigureRepository->show($email_configure);
    }


    /**
     * @OA\Put(
     *     path="/api/email-configure/{id}",
     *     summary="Update an email configuration",
     *     description="Update the details of an existing email configuration.",
     *     tags={"Email Configuration"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the email configuration to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="SMTP"),
     *             @OA\Property(property="smtp_host", type="string", example="smtp.example.com"),
     *             @OA\Property(property="smtp_port", type="integer", example=587),
     *             @OA\Property(property="smtp_user", type="string", example="user@example.com"),
     *             @OA\Property(property="smtp_password", type="string", example="password123"),
     *             @OA\Property(property="default_type", type="string", example="default"),
     *             @OA\Property(property="default_status", type="string", example="active"),
     *             @OA\Property(property="imap_host", type="string", example="imap.example.com"),
     *             @OA\Property(property="imap_port", type="integer", example=993),
     *             @OA\Property(property="imap_user", type="string", example="user@imap.com"),
     *             @OA\Property(property="imap_password", type="string", example="imapPassword123"),
     *             @OA\Property(property="sender_name", type="string", example="Sender Name"),
     *             @OA\Property(property="sender_email", type="string", example="sender@example.com"),
     *             @OA\Property(property="assign_method", type="string", example="auto"),
     *             @OA\Property(property="signature", type="string", example="Best regards, Team")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email configuration updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating email configuration")
     *         )
     *     )
     * )
     */
    public function update(Request $request, EmailConfigure $email_configure)
    {
        return $this->emailConfigureRepository->update($request, $email_configure);
    }

     /**
     * @OA\Delete(
     *     path="/api/email-configure/{id}",
     *     summary="Delete an email configuration",
     *     description="Delete an email configuration by its ID.",
     *     tags={"Email Configuration"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the email configuration to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email configuration deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration deleted successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email configuration not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email configuration not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting email configuration")
     *         )
     *     )
     * )
     */
    public function destroy(EmailConfigure $email_configure)
    {
        return $this->emailConfigureRepository->delete($email_configure);
    }
}
