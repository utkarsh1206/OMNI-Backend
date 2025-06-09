<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserActivityReportRepository;
use Illuminate\Http\Request;

class UserActivityReportController extends Controller
{
    protected $report;

    public function __construct(UserActivityReportRepository $report)
    {
        $this->report = $report;
    }

    /**
     * @OA\Get(
     *     path="/api/user-activity-report/list",
     *     summary="Get user activity report",
     *     description="Fetch user report between start and end date.",
     *     operationId="getUserActivityReport",
     *     tags={"User Activity Report"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=true,
     *         description="Start date of the report range.",
     *         @OA\Schema(
     *             type="string",
     *             format="date-time",
     *             example="2025-06-01 00:00:00"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=true,
     *         description="End date of the report range.",
     *         @OA\Schema(
     *             type="string",
     *             format="date-time",
     *             example="2025-06-30 23:59:59"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of records per page for pagination. Default is 15.",
     *         @OA\Schema(
     *             type="integer",
     *             example=15
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for paginated results. Default is 1.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User activity report data fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="login_date", type="string", example="2025-06-01"),
     *                 @OA\Property(property="user_activity", type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Missing required date filters or invalid input.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Missing startDate or endDate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Something went wrong while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error in fetching data."),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function list(Request $request)
    {
        return $this->report->getReport($request);
    }

    /**
     * @OA\Get(
     *     path="/api/user-activity-report/user-activity",
     *     summary="Get detailed user activity data",
     *     description="Fetch detailed activity data for a specific user on a given date, including login, call data, pause/resume activity, and more.",
     *     operationId="getUserActivityData",
     *     tags={"User Activity Report"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         description="ID of the user for whom the activity data is fetched.",
     *         @OA\Schema(
     *             type="integer",
     *             example=7
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="The specific date for the activity data.",
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-06-01"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User activity data fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Missing required parameters or invalid input.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Missing user_id or date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Something went wrong while processing the request.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching user activity data"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */

    public function userActivity(Request $request)
    {
        return $this->report->getData($request);
    }

    /**
     * @OA\Get(
     *     path="/api/user-activity-report/csv",
     *     summary="Export User Activity Report to CSV",
     *     description="Export user activity report data for a specific date range to a CSV file. Includes login data, call history, pause/resume activity, and more.",
     *     operationId="exportUserActivityReportToCsv",
     *     tags={"User Activity Report"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=true,
     *         description="The start date for the report period.",
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-06-01 00:00:00"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=true,
     *         description="The end date for the report period.",
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-06-30 23:59:59"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CSV file containing the user activity report has been successfully generated.",
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Missing required parameters or invalid input.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Missing startDate or endDate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Something went wrong while generating the CSV report.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error exporting report to CSV"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function exportCsv(Request $request)
    {
        return $this->report->exportReportToCsv($request);
    }

    /**
     * @OA\Get(
     *     path="/api/user-activity-report/graph",
     *     summary="Get Graph Data for User Activity",
     *     description="Fetch the total counts for Pause, Resume, Incoming Calls, and Outgoing Calls for a specific date range.",
     *     operationId="getGraphsRecord",
     *     tags={"User Activity Report"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         required=true,
     *         description="The start date for the report period.",
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-06-01 00:00:00"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         required=true,
     *         description="The end date for the report period.",
     *         @OA\Schema(
     *             type="string",
     *             format="date",
     *             example="2025-06-30 23:59:59"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved graph data for the specified period.",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request. Missing required parameters or invalid input.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="error", type="string", example="Missing startDate or endDate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. Something went wrong while fetching the graph data.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching graph data"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */

    public function graphData(Request $request)
    {
        return $this->report->getGraphsRecord($request);
    }
}
