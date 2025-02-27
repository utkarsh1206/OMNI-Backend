<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicketNotification;
use App\Repositories\SupportTicketNotificationRepository;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="SupportTicketNotifications",
 *     description="Operations related to Support Ticket Notifications"
 * )
 */
class SupportTicketNotificationController extends Controller
{

    private $supportTicketNotificationRepository;

    public function __construct(SupportTicketNotificationRepository $supportTicketNotificationRepository)
    {
        $this->supportTicketNotificationRepository = $supportTicketNotificationRepository;
    }
    /**
     * @OA\Get(
     *     path="/api/notification",
     *     tags={"SupportTicketNotifications"},
     *     summary="Get support ticket notifications",
     *     description="Retrieve a list of support ticket notifications, filtered by support ticket ID.",
     *     @OA\Parameter(
     *         name="supportticket_id",
     *         in="query",
     *         required=false,
     *         description="Filter notifications by support ticket ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of support ticket notifications",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="support_ticket_id", type="integer"),
     *                     @OA\Property(property="notification_message", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(Request $request)
    {
     return $this->supportTicketNotificationRepository->list($request);
    }
}
