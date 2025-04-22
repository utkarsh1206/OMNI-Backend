<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\CallLogReportRepository;

/**
 * @OA\Tag(
 *     name="Call Log Report",
 *     description="APIs for call log reporting, export and summaries"
 * )
 */

class CallLogReportController extends Controller
{
    protected $report;

    public function __construct(CallLogReportRepository $report)
    {
        $this->report = $report;
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/list",
     *     tags={"Call Log Report"},
     *     summary="Get call log list",
     *     security={{"bearerAuth": {}}},
     *     description="Returns a paginated or full list of call logs based on filters",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date time "
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date time"
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data has been fetched successfully !",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */

    public function list(Request $request)
    {
        return $this->report->getCallReport($request);
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/csv",
     *     tags={"Call Log Report"},
     *     summary="Export call logs to CSV",
     *     security={{"bearerAuth": {}}},
     *     description="Exports call logs in CSV format based on provided filters",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CSV file exported successfully",
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
     *         description="Bad Request"
     *     )
     * )
     */

    public function exportCsv(Request $request)
    {
        return $this->report->exportCsvData($request);
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/chart-summary",
     *     tags={"Call Log Report"},
     *     summary="Get call chart summary",
     *     security={{"bearerAuth": {}}},
     *     description="Returns summary of calls for charting purposes",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data has been fetched successfully !",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     * )
     */

    public function getChartSummary(Request $request)
    {
        return $this->report->getChartData($request);
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/status-summary",
     *     tags={"Call Log Report"},
     *     summary="Get call status summary",
     *     security={{"bearerAuth": {}}},
     *     description="Returns the distribution of call statuses",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data has been fetched successfully !",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     * )
     */
    public function getStatusSummary(Request $request)
    {
        return $this->report->getCallStatusDistribution($request);
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/usergroup-summary",
     *     tags={"Call Log Report"},
     *     summary="Get calls by user group",
     *     security={{"bearerAuth": {}}},
     *     description="Returns call counts grouped by user group",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data has been fetched successfully !",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     * )
     */
    public function getByUserGroup(Request $request)
    {
        return $this->report->getCallsByUserGroup($request);
    }

    /**
     * @OA\Get(
     *     path="/api/call-log-report/user-summary",
     *     tags={"Call Log Report"},
     *     summary="Get calls by agent",
     *     security={{"bearerAuth": {}}},
     *     description="Returns call counts grouped by agent",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="campaign_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by campaign ID"
     *     ),
     *     @OA\Parameter(
     *         name="agent",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by agent username"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data has been fetched successfully !",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(property="data", type="object"),
     *         )
     *     ),
     * )
     */
    public function getCallsByAgent(Request $request)
    {
        return $this->report->getCallsByAgent($request);
    }
}
