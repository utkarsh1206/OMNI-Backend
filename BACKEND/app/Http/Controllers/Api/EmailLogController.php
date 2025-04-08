<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\EmailLogRepository;
use App\Http\Resources\EmailLogResource;

class EmailLogController extends Controller
{
    protected $emailLogRepository;

    public function __construct(EmailLogRepository $emailLogRepository)
    {
        $this->emailLogRepository = $emailLogRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/email-logs",
     *     operationId="getEmailLogsList",
     *     tags={"Email Logs"},
     *     summary="Get list of email logs",
     *     description="Returns list of email logs with filters, pagination, and search",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully !"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="email", type="string", example="example@example.com"),
     *                         @OA\Property(property="subject", type="string", example="Email subject"),
     *                         @OA\Property(property="status", type="string", example="sent"),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/email-logs?page=1"),
     *                 @OA\Property(property="last_page", type="integer", example=2),
     *                 @OA\Property(property="next_page_url", type="string", example="http://localhost:8000/api/email-logs?page=2"),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="total", type="integer", example=11)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
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
     *     operationId="getEmailLogById",
     *     tags={"Email Logs"},
     *     summary="Get specific email log by ID",
     *     description="Returns a single email log by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Email log ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="example@example.com"),
     *                 @OA\Property(property="subject", type="string", example="Email subject"),
     *                 @OA\Property(property="status", type="string", example="sent"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-28T07:40:40.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email log not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */
    public function show($id)
    {
        $log = $this->emailLogRepository->findById($id);
        return new EmailLogResource($log);
    }

    /**
     * @OA\Post(
     *     path="/api/email-logs/export",
     *     operationId="exportEmailLogsToCsv",
     *     tags={"Email Logs"},
     *     summary="Export email logs to CSV",
     *     description="Exports the email logs to a CSV file",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful export",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Email logs exported successfully"),
     *             @OA\Property(property="url", type="string", example="http://localhost:8000/path/to/csv")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *     )
     * )
     */
    public function exportCsv(Request $request)
    {
        return $this->emailLogRepository->exportToCsv($request);
    }
}
