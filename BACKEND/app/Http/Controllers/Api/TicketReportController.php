<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\TicketReportRepository;

class TicketReportController extends Controller
{
    private $ticketReportRepository;

    public function __construct(TicketReportRepository $ticketReportRepository)
    {
        $this->ticketReportRepository = $ticketReportRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/ticketReport",
     *     summary="Fetch Support Ticket Reports",
     *     description="Fetches a list of support tickets with optional filters and pagination. It also supports error handling for invalid requests.",
     *     operationId="ticketReport",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of tickets per page (default is 10)",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number to retrieve (default is 1), or 'all' for all tickets",
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved ticket reports",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetch successful"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="ticket_id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="priority", type="string"),
     *                     @OA\Property(property="assignedAgent", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function ticketReport(Request $request)
    {
        return $this->ticketReportRepository->ticketReport($request);
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/summary/category",
     *     summary="Fetch Category Summary for Support Tickets",
     *     description="Fetches the summary of support tickets grouped by category, including the status breakdown for each category.",
     *     operationId="categorySummary",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved category summary data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="string", example="Success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Category", type="string"),
     *                     @OA\Property(property="Total Tickets", type="integer"),
     *                     @OA\Property(property="Open", type="integer"),
     *                     @OA\Property(property="Resolved", type="integer"),
     *                     @OA\Property(property="Pending", type="integer"),
     *                     @OA\Property(property="Closed", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function categorySummary(Request $request)
    {
        return $this->ticketReportRepository->categorySummary($request);
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/summary/priority",
     *     summary="Fetch Summary of Support Tickets by Priority",
     *     description="Fetches the summary of support tickets grouped by priority, with the total ticket count for each priority level.",
     *     operationId="prioritySummary",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved priority summary data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="string", example="Success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Priority", type="string"),
     *                     @OA\Property(property="Total Tickets", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function prioritySummary(Request $request)
    {
        return $this->ticketReportRepository->prioritySummary($request);
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/summary/status",
     *     summary="Fetch Summary of Support Tickets by Status",
     *     description="Fetches the summary of support tickets grouped by status, with the total ticket count for each status.",
     *     operationId="statusSummary",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved status summary data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="string", example="Success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Status", type="string"),
     *                     @OA\Property(property="Total Tickets", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function statusSummary(Request $request)
    {
        return $this->ticketReportRepository->statusSummary($request);
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/summary/weekday",
     *     summary="Fetch Summary of Support Tickets by Weekday",
     *     description="Fetches the summary of support tickets grouped by the weekday they were created, showing the total number of tickets for each weekday of the current week.",
     *     operationId="weekdaySummary",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved weekday summary data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="string", example="Success"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="Weekday", type="string"),
     *                     @OA\Property(property="Total Tickets", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function weekdaySummary(Request $request)
    {
        return $this->ticketReportRepository->weekdaySummary($request);
    }

    /**
     * @OA\Get(
     *     path="/api/ticketReport/csv",
     *     summary="Download Ticket Report as CSV",
     *     description="Generates a CSV report of support tickets filtered by various parameters and allows the user to download it.",
     *     operationId="ticketReportDownload",
     *     tags={"Ticket Report"},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by start date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by end date (YYYY-MM-DD format)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="ticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by priority level",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter tickets by category type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CSV file of ticket report is generated and ready for download",
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(
     *                 type="string",
     *                 description="CSV file content",
     *                 example="Ticket ID,Customer Name,Category,Priority,Status,Created Date,Last Updated,Assigned Agent,Resolution Time,Comments,Escalation\n1,John Doe,Technical,High,Open,2025-03-01,2025-03-02,Agent A,2 hours,No,No"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid parameters or missing authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, missing or invalid authentication token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error, exception while processing request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request.")
     *         )
     *     ),
     * )
     */
    public function exportCsv(Request $request)
    {
        return $this->ticketReportRepository->exportCsv($request);
    }
}
