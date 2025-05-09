<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Services\SlaService;
use App\Repositories\SupportTicketRepository;
use App\Http\Requests\SupportTicketRequest;

class SupportTicketController extends Controller
{
    protected $slaService;

    private $slaReportRepository;

    // Constructor to inject the SlaService
    public function __construct(SlaService $slaService, SupportTicketRepository $slaReportRepository)
    {
        $this->slaService = $slaService;
        $this->slaReportRepository = $slaReportRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/support-ticket",
     *     summary="Retrieve a list of support tickets",
     *     description="This API endpoint retrieves a paginated or full list of support tickets with filtering options.",
     *     operationId="listSupportTickets",
     *     tags={"Support Ticket"},
     *
     *     @OA\Parameter(name="search", in="query", description="Search by to_emails, subject, or description", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="ticket_id", in="query", description="Filter by ticket ID", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="product_id", in="query", description="Filter by product ID", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="subject", in="query", description="Filter by subject", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="default_type", in="query", description="Filter by default type", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="default_status", in="query", description="Filter by default status", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="default_priority", in="query", description="Filter by default priority", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="assign_to_id", in="query", description="Filter by assign_to_id", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contact_id", in="query", description="Filter by contact_id", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", description="Number of results per page", required=false, @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="page", in="query", description="Page number or 'all' to fetch all records", required=false, @OA\Schema(type="string")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of support tickets retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="support_ticket_id", type="string", example="130424123456"),
     *                     @OA\Property(property="product_id", type="integer", example=5),
     *                     @OA\Property(property="subject", type="string", example="Unable to access the dashboard"),
     *                     @OA\Property(property="default_type", type="string", example="Issue"),
     *                     @OA\Property(property="default_status", type="string", example="Open"),
     *                     @OA\Property(property="default_priority", type="string", example="High"),
     *                     @OA\Property(property="to_emails", type="string", example="user@example.com"),
     *                     @OA\Property(property="description", type="string", example="User is experiencing issues accessing the dashboard."),
     *                     @OA\Property(property="attachment", type="array", @OA\Items(type="string", example="https://yourdomain.com/storage/support_ticket/file1.pdf")),
     *                     @OA\Property(property="assign_to_id", type="integer", example=2),
     *                     @OA\Property(property="contact_id", type="integer", example=3),
     *                     @OA\Property(property="department_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-13T08:30:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->slaReportRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/support-ticket",
     *     summary="Create a new support ticket",
     *     description="This API endpoint creates a new support ticket.",
     *     operationId="storeSupportTicket",
     *     tags={"Support Ticket"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject", "description", "product_id", "status", "priority"},
     *             @OA\Property(property="subject", type="string", example="Issue with product X"),
     *             @OA\Property(property="description", type="string", example="Detailed description of the issue."),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="priority_id", type="integer", example=2),
     *             @OA\Property(property="ticket_type_id", type="integer", example=1),
     *             @OA\Property(property="to_emails", type="array", items=@OA\Items(type="string", example="user@example.com")),
     *             @OA\Property(property="attachment", type="array", items=@OA\Items(type="string", example="https://yourdomain.com/storage/attachments/file1.pdf")),
     *             @OA\Property(property="assign_to_id", type="integer", example=2),
     *             @OA\Property(property="contact_id", type="integer", example=3),
     *             @OA\Property(property="department_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Support ticket created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="subject", type="string", example="Issue with product X"),
     *                 @OA\Property(property="description", type="string", example="Detailed description of the issue."),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="open"),
     *                 @OA\Property(property="priority", type="string", example="high"),
     *                 @OA\Property(property="to_emails", type="array", items=@OA\Items(type="string", example="user@example.com")),
     *                 @OA\Property(property="attachment", type="array", items=@OA\Items(type="string", example="https://yourdomain.com/storage/attachments/file1.pdf")),
     *                 @OA\Property(property="assign_to_id", type="integer", example=2),
     *                 @OA\Property(property="contact_id", type="integer", example=3),
     *                 @OA\Property(property="department_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-13T08:30:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Ticket created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(SupportTicketRequest $request)
    {
        return $this->slaReportRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/support-tickets/{id}",
     *     summary="Get support ticket details",
     *     description="Fetch a specific support ticket by its ID and return detailed ticket data.",
     *     operationId="getSupportTicket",
     *     tags={"Support Ticket"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the support ticket",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Support Ticket Details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="campaign_id", type="integer", example=2),
     *                 @OA\Property(property="inbound_group_id", type="integer", example=3),
     *                 @OA\Property(property="contact_id", type="integer", example=4),
     *                 @OA\Property(property="assign_to_id", type="integer", example=5),
     *                 @OA\Property(property="default_type", type="string", example="issue"),
     *                 @OA\Property(property="default_priority", type="string", example="high"),
     *                 @OA\Property(property="default_status", type="string", example="in_progress"),
     *                 @OA\Property(property="subject", type="string", example="Ticket subject"),
     *                 @OA\Property(property="description", type="string", example="Ticket description"),
     *                 @OA\Property(property="department_id", type="integer", example=6),
     *                 @OA\Property(property="to_emails", type="string", example="to@example.com"),
     *                 @OA\Property(property="cc", type="string", example="cc@example.com"),
     *                 @OA\Property(property="bcc", type="string", example="bcc@example.com"),
     *                 @OA\Property(property="incident_id", type="integer", example=7),
     *                 @OA\Property(property="account_id", type="integer", example=8),
     *                 @OA\Property(
     *                     property="attachment",
     *                     type="array",
     *                     @OA\Items(type="string", example="https://yourdomain.com/storage/attachments/filename.pdf")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-12T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T09:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Support Ticket not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Support ticket not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->show($supportTicket);
    }

    /**
     * @OA\Put(
     *     path="/api/support-ticket/{id}",
     *     summary="Update an existing support ticket",
     *     description="This endpoint updates an existing support ticket, processes attachments, sends emails, and logs the operation.",
     *     operationId="updateSupportTicket",
     *     tags={"Support Ticket"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the support ticket to update",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"subject", "description"},
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="campaign_id", type="integer", example=2),
     *                 @OA\Property(property="inbound_group_id", type="integer", example=3),
     *                 @OA\Property(property="contact_id", type="integer", example=4),
     *                 @OA\Property(property="assign_to_id", type="integer", example=5),
     *                 @OA\Property(property="priority_id", type="integer", example=2),
     *                 @OA\Property(property="ticket_type_id", type="integer", example=1),
     *                 @OA\Property(property="subject", type="string", example="Updated subject line"),
     *                 @OA\Property(property="description", type="string", example="Updated ticket description"),
     *                 @OA\Property(property="department_id", type="integer", example=6),
     *                 @OA\Property(property="to_emails", type="string", format="email", example="to@example.com"),
     *                 @OA\Property(property="cc", type="string", example="cc@example.com"),
     *                 @OA\Property(property="bcc", type="string", example="bcc@example.com"),
     *                 @OA\Property(property="incident_id", type="integer", example=7),
     *                 @OA\Property(property="account_id", type="integer", example=8),
     *                 @OA\Property(
     *                     property="attachment",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Support Ticket Updated Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Support Ticket Updated Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="campaign_id", type="integer", example=2),
     *                 @OA\Property(property="inbound_group_id", type="integer", example=3),
     *                 @OA\Property(property="contact_id", type="integer", example=4),
     *                 @OA\Property(property="assign_to_id", type="integer", example=5),
     *                 @OA\Property(property="default_type", type="string", example="issue"),
     *                 @OA\Property(property="default_priority", type="string", example="high"),
     *                 @OA\Property(property="default_status", type="string", example="in_progress"),
     *                 @OA\Property(property="subject", type="string", example="Updated subject"),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="department_id", type="integer", example=6),
     *                 @OA\Property(property="to_emails", type="string", example="to@example.com"),
     *                 @OA\Property(property="cc", type="string", example="cc@example.com"),
     *                 @OA\Property(property="bcc", type="string", example="bcc@example.com"),
     *                 @OA\Property(property="incident_id", type="integer", example=7),
     *                 @OA\Property(property="account_id", type="integer", example=8),
     *                 @OA\Property(
     *                     property="attachment",
     *                     type="array",
     *                     @OA\Items(type="string", example="https://yourdomain.com/storage/attachments/filename.pdf")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-12T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T09:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send email",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Failed to send email"),
     *             @OA\Property(property="error", type="string", example="SMTP connection failed")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Support Ticket not found"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function update(SupportTicketRequest $request, SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->update($request, $supportTicket);
    }

    /**
     * @OA\Delete(
     *     path="/api/support-ticket/{id}",
     *     summary="Delete a support ticket",
     *     description="Deletes a specific support ticket by ID.",
     *     operationId="deleteSupportTicket",
     *     tags={"Support Ticket"},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the ticket to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Support ticket deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Ticket deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found"
     *     )
     * )
     */
    public function destroy(SupportTicket $supportTicket)
    {
        return $this->slaReportRepository->delete($supportTicket);
    }

    /**
     * @OA\Patch(
     *     path="/api/update-ticket-status/{id}",
     *     summary="Update the status, priority, or type of a support ticket",
     *     description="This endpoint allows you to update the status, priority, or type of a specific support ticket. Sends email notifications when the ticket status is changed to 'Resolved'.",
     *     operationId="statusUpdateSupportTicket",
     *     tags={"Support Ticket"},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the support ticket",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="default_status",
     *         in="query",
     *         description="New status for the ticket (e.g., 'Resolved', 'In Progress')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="default_priority",
     *         in="query",
     *         description="New priority for the ticket (e.g., 'High', 'Low', 'Medium')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="default_type",
     *         in="query",
     *         description="New type for the ticket (e.g., 'Issue', 'Bug', 'Feature Request')",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Support ticket status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Support ticket status updated successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="ticket",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="campaign_id", type="integer", example=2),
     *                     @OA\Property(property="default_status", type="string", example="Resolved"),
     *                     @OA\Property(property="default_priority", type="string", example="High"),
     *                     @OA\Property(property="default_type", type="string", example="Issue"),
     *                     @OA\Property(property="subject", type="string", example="Ticket subject"),
     *                     @OA\Property(property="description", type="string", example="Ticket description"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-13T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request (Invalid Parameters)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Support Ticket not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Support ticket not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Error occurred while processing the request")
     *         )
     *     )
     * )
     */
    public function statusUpdate(Request $request, $supportTicket)
    {
        return $this->slaReportRepository->statusUpdate($request, $supportTicket);
    }
}
