<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailLog;
use App\Http\Resources\EmailLogResource;
use App\Repositories\EmailLogRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;


class EmailLogController extends Controller
{

    private $emailLogRepository;

    public function __construct(EmailLogRepository $emailLogRepository)
    {
        $this->emailLogRepository = $emailLogRepository;
    }
     /**
     * @OA\Get(
     *     path="/api/email-logs",
     *     summary="Get all email logs with filters",
     *     description="Retrieve email logs with optional filters such as search term, status, user id, and date",
     *     operationId="getEmailLogs",
     *     tags={"Email Logs"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for email template subject or user details",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by email log status (e.g., sent, failed)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by creation date (format: yyyy-mm-dd)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination. Use 'all' to fetch all records without pagination.",
     *         required=false,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of email logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="meta", type="object", 
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters"
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->emailLogRepository->list($request);
    }

     /**
     * @OA\Get(
     *     path="/api/email-logs/{id}",
     *     summary="Get a specific email log",
     *     description="Retrieve the details of a specific email log by ID",
     *     operationId="getEmailLogById",
     *     tags={"Email Logs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the email log",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details of the email log"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email log not found"
     *     )
     * )
     */
    public function show($id)
    {
        return $this->emailLogRepository->show($id);
    }
    /**
     * Export email logs to CSV
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportCsv(Request $request)
    {
       return $this->emailLogRepository->exportCsv($request);
    }
}