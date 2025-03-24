<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Department;
use App\Models\IncidentType;
use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
{

    private $dashboardRepository;

    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }
  

    /**
 * @OA\Get(
 *     path="/api/dashboard/ticket_details",
 *     summary="Get dashboard statistics",
 *     description="Fetches various statistics including total users, accounts, tickets, departments, and detailed ticket breakdowns.",
 *     operationId="getDashboardData",
 *     tags={"Dashboard"},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="total_users", type="integer", example=150),
 *             @OA\Property(property="total_accounts", type="integer", example=50),
 *             @OA\Property(property="total_pending_tickets", type="integer", example=20),
 *             @OA\Property(property="total_departments", type="integer", example=5),
 *             @OA\Property(property="total_contacts", type="integer", example=200),
 *             @OA\Property(property="total_ticket_count", type="integer", example=500),
 *
 *             @OA\Property(property="tickets_by_status", type="object",
 *                 @OA\AdditionalProperties(
 *                     type="object",
 *                     @OA\Property(property="count", type="integer", example=120)
 *                 )
 *             ),
 *
 *             @OA\Property(property="tickets_by_priority", type="object",
 *                 @OA\AdditionalProperties(
 *                     type="object",
 *                     @OA\Property(property="count", type="integer", example=50)
 *                 )
 *             ),
 *
 *             @OA\Property(property="tickets_by_department", type="object",
 *                 @OA\AdditionalProperties(
 *                     type="object",
 *                     @OA\Property(property="count", type="integer", example=30)
 *                 )
 *             ),
 *
 *             @OA\Property(property="pending_tickets_by_agent", type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="assign_to_id", type="integer", example=12),
 *                     @OA\Property(property="count", type="integer", example=5),
 *                     @OA\Property(property="agent_name", type="string", example="John Doe")
 *                 )
 *             ),
 *
 *             @OA\Property(property="tickets_by_incident_type", type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="incident_id", type="integer", example=3),
 *                     @OA\Property(property="count", type="integer", example=40),
 *                     @OA\Property(property="incident_name", type="string", example="Server Downtime")
 *                 )
 *             ),
 *
 *             @OA\Property(property="tickets_by_product", type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="product_id", type="integer", example=7),
 *                     @OA\Property(property="count", type="integer", example=25)
 *                 )
 *             ),
 *
 *             @OA\Property(property="tickets_by_type", type="object",
 *                 @OA\AdditionalProperties(
 *                     type="object",
 *                     @OA\Property(property="count", type="integer", example=100)
 *                 )
 *             ),
 *
 *             @OA\Property(property="activity_summary", type="object",
 *                 @OA\Property(property="today", type="integer", example=10),
 *                 @OA\Property(property="last_7_days", type="integer", example=60),
 *                 @OA\Property(property="last_30_days", type="integer", example=200)
 *             ),
 *
 *             @OA\Property(property="deleted_ticket_count", type="integer", example=5)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request"
 *     )
 * )
 */


    public function index(Request $request)
    {
        return $this->dashboardRepository->index($request);
    }
}
