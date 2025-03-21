<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailTemplateRequest;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Resources\MailTemplateResource;
use App\Repositories\MailTemplateRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class MailTemplateController extends Controller
{

    private $mailTemplateRepository;

    public function __construct(MailTemplateRepository $mailTemplateRepository)
    {
        $this->mailTemplateRepository = $mailTemplateRepository;
    }


    /**
     * @OA\Get(
     *     path="/mail-templates",
     *     summary="List Mail Templates with optional search and pagination",
     *     description="Fetches a list of mail templates with optional search filters and pagination.",
     *     operationId="listMailTemplates",
     *     tags={"Mail Templates"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter mail templates by title, type, subject, or mail_for.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page for pagination.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number to fetch. Use 'all' to fetch all records without pagination.",
     *         required=false,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved mail templates.",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="meta", type="object", 
     *                     @OA\Property(property="current_page", type="integer"),
     *                     @OA\Property(property="last_page", type="integer"),
     *                     @OA\Property(property="per_page", type="integer"),
     *                     @OA\Property(property="total", type="integer")
     *                 ),
     *                 @OA\Property(property="data", type="array", 
     *                     @OA\Items(
     *                         type="object",
     *                         properties={
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="title", type="string"),
     *                             @OA\Property(property="type", type="string"),
     *                             @OA\Property(property="subject", type="string"),
     *                             @OA\Property(property="mail_for", type="string"),
     *                             @OA\Property(property="body", type="string")
     *                         }
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid query parameters."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error."
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->mailTemplateRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/mail-templates",
     *     summary="Create a new Mail Template",
     *     description="Creates a new mail template by receiving the template details in the request body.",
     *     operationId="storeMailTemplate",
     *     tags={"Mail Templates"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Mail template data to be created",
     *         @OA\JsonContent(
     *             required={"title", "type", "subject", "mail_for", "body"},
     *             type="object",
     *             properties={
     *                 @OA\Property(property="title", type="string", description="Title of the mail template"),
     *                 @OA\Property(property="type", type="string", description="Type of the mail template (e.g., notification, welcome, etc.)"),
     *                 @OA\Property(property="subject", type="string", description="Subject of the email template"),
     *                 @OA\Property(property="mail_for", type="string", description="What the template is used for (e.g., user registration, password reset, etc.)"),
     *                 @OA\Property(property="body", type="string", description="Body of the mail template"),
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mail Template created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail Template created successfully!")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Validation error"),
     *                 @OA\Property(property="errors", type="object")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(MailTemplateRequest $request)
    {
        try {
            $mailTemplate = MailTemplate::create($request->all());
            return response()->json([
                'message' => ' Mail Template created successfully!',
                'data' => new MailTemplateResource($mailTemplate)
            ], 201);
        } catch (ValidationException $e) {
            // Custom error response
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/mail-templates/{id}",
     *     summary="Get a Mail Template by ID",
     *     description="Fetch a specific mail template by its ID.",
     *     operationId="showMailTemplate",
     *     tags={"Mail Templates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the mail template to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the mail template",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="id", type="integer", description="ID of the mail template"),
     *                 @OA\Property(property="title", type="string", description="Title of the mail template"),
     *                 @OA\Property(property="type", type="string", description="Type of the mail template"),
     *                 @OA\Property(property="subject", type="string", description="Subject of the email template"),
     *                 @OA\Property(property="mail_for", type="string", description="Purpose of the mail template"),
     *                 @OA\Property(property="body", type="string", description="Body of the email template")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mail Template not found",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail Template not found")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function show(MailTemplate $mailTemplate)
    {
        return $this->mailTemplateRepository->show($mailTemplate);
    }

    /**
     * @OA\Put(
     *     path="/mail-templates/{id}",
     *     summary="Update a Mail Template",
     *     description="Update the details of a specific mail template by its ID.",
     *     operationId="updateMailTemplate",
     *     tags={"Mail Templates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the mail template to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Mail template data to update",
     *         @OA\JsonContent(
     *             required={"title", "type", "subject", "mail_for", "body"},
     *             type="object",
     *             properties={
     *                 @OA\Property(property="title", type="string", description="Title of the mail template"),
     *                 @OA\Property(property="type", type="string", description="Type of the mail template (e.g., notification, welcome, etc.)"),
     *                 @OA\Property(property="subject", type="string", description="Subject of the email template"),
     *                 @OA\Property(property="mail_for", type="string", description="What the template is used for (e.g., user registration, password reset, etc.)"),
     *                 @OA\Property(property="body", type="string", description="Body of the mail template"),
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mail Template updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template updated successfully!")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mail Template not found",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template not found.")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Validation error"),
     *                 @OA\Property(property="errors", type="object")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(MailTemplateRequest $request, $id)
    {
        return $this->mailTemplateRepository->update($request, $id);
    }

     /**
     * @OA\Delete(
     *     path="/mail-templates/{id}",
     *     summary="Delete a Mail Template by ID",
     *     description="Delete a specific mail template by its ID. If the template is not found or cannot be deleted, an appropriate error is returned.",
     *     operationId="deleteMailTemplate",
     *     tags={"Mail Templates"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the mail template to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mail template deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template deleted successfully"),
     *                 @OA\Property(property="data", type="object", description="Mail template that was deleted")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mail Template not found",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template not found")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Mail Template deletion not allowed",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Mail template cannot be deleted")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy($id)
    {
        return $this->mailTemplateRepository->delete($id);
    }
}
