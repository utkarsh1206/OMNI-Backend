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

    public function list(Request $request)
    {
        return $this->report->getReport($request);
    }

    public function userActivity(Request $request)
    {
        return $this->report->getData($request);
    }

    public function exportCsv(Request $request)
    {
        return $this->report->exportReportToCsv($request);
    }
}
