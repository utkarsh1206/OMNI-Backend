<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\SlaReportRepository;

class SlaReportController extends Controller
{
    private $slaReportRepository;

    public function __construct(SlaReportRepository $slaReportRepository)
    {
        $this->slaReportRepository = $slaReportRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/slareport",
     *     summary="Generate a report of support tickets",
     *     description="Generates a report of support tickets based on various filters",
     *     operationId="generateReport",
     *     tags={"SLA Reports"},
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter tickets created after this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter tickets created before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by ticket priority",
     *         required=false,
     *         @OA\Schema(type="string", example="High")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by ticket category",
     *         required=false,
     *         @OA\Schema(type="string", example="Technical")
     *     ),
     *     @OA\Parameter(
     *         name="slaCompliance",
     *         in="query",
     *         description="Filter by SLA compliance status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter tickets assigned to a specific user",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully generated report",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="ticket_id", type="string", example="TICKET-12345"),
     *                 @OA\Property(property="category", type="string", example="Technical"),
     *                 @OA\Property(property="priority", type="string", example="High"),
     *                 @OA\Property(property="opened_date", type="string", format="date-time", example="2025-03-01T12:00:00"),
     *                 @OA\Property(property="sla_compliance", type="boolean", example=true),
     *                 @OA\Property(property="resolve_time_value", type="string", example="02:30:00"),
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function generateReport(Request $request)
    {
        return $this->slaReportRepository->generateReport($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sla-priority",
     *     summary="Get the count of escalated support tickets grouped by priority",
     *     description="This endpoint returns the count of support tickets that are escalated, grouped by their priority level. Supports filtering based on priority, category, SLA compliance, and date range.",
     *     operationId="prioritySLA",
     *     tags={"SLA Reports"},
     *
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter tickets created after this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter tickets created before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter tickets by priority",
     *         required=false,
     *         @OA\Schema(type="string", example="High")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter tickets by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Technical")
     *     ),
     *     @OA\Parameter(
     *         name="slaCompliance",
     *         in="query",
     *         description="Filter tickets by SLA compliance",
     *         required=false,
     *         @OA\Schema(type="string", example="Yes")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter tickets assigned to a specific user",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched priority-wise ticket count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="priority", type="string", example="High"),
     *                 @OA\Property(property="ticket_count", type="integer", example=50)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function prioritySLA(Request $request)
    {
        return $this->slaReportRepository->prioritySLA($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sla-category",
     *     summary="Get the count of escalated support tickets grouped by category",
     *     description="This endpoint returns the count of support tickets that are escalated, grouped by their category. Supports filtering based on priority, category, SLA compliance, and date range.",
     *     operationId="categorySLA",
     *     tags={"SLA Reports"},
     *
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter tickets created after this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter tickets created before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter tickets by priority",
     *         required=false,
     *         @OA\Schema(type="string", example="High")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter tickets by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Technical")
     *     ),
     *     @OA\Parameter(
     *         name="slaCompliance",
     *         in="query",
     *         description="Filter tickets by SLA compliance",
     *         required=false,
     *         @OA\Schema(type="string", example="Yes")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter tickets assigned to a specific user",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched category-wise ticket count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="category", type="string", example="Technical"),
     *                 @OA\Property(property="ticket_count", type="integer", example=50)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function categorySLA(Request $request)
    {
        return $this->slaReportRepository->categorySLA($request);
    }

    /**
     * @OA\Get(
     *     path="/api/sla-incident",
     *     summary="Get the count of escalated support tickets grouped by incident type",
     *     description="This endpoint returns the count of escalated support tickets grouped by incident type. Supports filtering based on priority, category, SLA compliance, and date range.",
     *     operationId="incidentSLA",
     *     tags={"SLA Reports"},
     *
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter tickets created after this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter tickets created before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter tickets by priority",
     *         required=false,
     *         @OA\Schema(type="string", example="High")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter tickets by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Technical")
     *     ),
     *     @OA\Parameter(
     *         name="slaCompliance",
     *         in="query",
     *         description="Filter tickets by SLA compliance",
     *         required=false,
     *         @OA\Schema(type="string", example="Yes")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter tickets assigned to a specific user",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched incident-wise ticket count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="data", type="array", items=@OA\Items(
     *                 type="object",
     *                 @OA\Property(property="incident_id", type="integer", example=1),
     *                 @OA\Property(property="incident_name", type="string", example="Network Issue"),
     *                 @OA\Property(property="ticket_count", type="integer", example=50)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function incidentSLA(Request $request)
    {
        return $this->slaReportRepository->incidentSLA($request);
    }

    /**
     * @OA\Get(
     *     path="/api/slareport/csv",
     *     summary="Export support tickets as CSV",
     *     description="This endpoint allows users to export support ticket data in CSV format. It supports filtering by various parameters such as date range, priority, category, SLA compliance, and user.",
     *     operationId="exportCsv",
     *     tags={"SLA Reports"},
     *
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Filter tickets created after this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="Filter tickets created before this date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter tickets by priority",
     *         required=false,
     *         @OA\Schema(type="string", example="High")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter tickets by category",
     *         required=false,
     *         @OA\Schema(type="string", example="Technical")
     *     ),
     *     @OA\Parameter(
     *         name="slaCompliance",
     *         in="query",
     *         description="Filter tickets by SLA compliance",
     *         required=false,
     *         @OA\Schema(type="string", example="Yes")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter tickets assigned to a specific user",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully generated CSV file with filtered ticket data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="CSV file generated successfully")
     *         ),
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to invalid parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function exportCsv(Request $request)
    {
        return $this->slaReportRepository->exportCsv($request);
    }
}
