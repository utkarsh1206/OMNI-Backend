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

    public function index()
    {
        return $this->superAdminDashboardRepo->index();
    }
}
