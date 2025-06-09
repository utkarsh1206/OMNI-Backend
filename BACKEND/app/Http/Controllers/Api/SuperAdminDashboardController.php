<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\SuperAdminDashboardRepository;

class SuperAdminDashboardController extends Controller
{
    private $superAdminDashboardRepo;

    public function __construct(SuperAdminDashboardRepository $superAdminDashboardRepo)
    {
        $this->superAdminDashboardRepo = $superAdminDashboardRepo;
    }

    /**
     * @OA\Get(
     *     path="/api/super-admin-dashboard",
     *     summary="Get Super Admin Dashboard Data",
     *     description="Fetch the Super Admin Dashboard data, including tenants and license information.",
     *     operationId="getSuperAdminDashboardData",
     *     tags={"Super Admin Dashboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Super Admin Dashboard data fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching data.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return $this->superAdminDashboardRepo->index();
    }
}
