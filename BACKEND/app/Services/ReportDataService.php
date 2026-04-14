<?php

namespace App\Services;

use App\Repositories\CallLogReportRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\TicketReportRepository;
use App\Repositories\SlaReportRepository;
use App\Repositories\UserActivityReportRepository;
use Illuminate\Http\Request;

class ReportDataService
{
    public function generate($connectionName, $report_name, array $payload, $domain)
    {
        $request = new Request($payload);

        if ($report_name == "Ticket Report") {
            $response = app(TicketReportRepository::class)
                ->exportCsv($request, $connectionName);
        }

        if ($report_name == "Call Logs") {
            $response = app(CallLogReportRepository::class)
                ->exportCsvData($request, $connectionName, $domain);
        }
        if ($report_name == "Email Logs") {
            $response = app(EmailLogRepository::class)
                ->exportToCsv($request, $connectionName);
        }
        if ($report_name == "SLA Report") {
            $response = app(SlaReportRepository::class)
                ->exportCsv($request, $connectionName);
        }
        if ($report_name == "User Activity Report") {
            $response = app(UserActivityReportRepository::class)
                ->exportReportToCsv($request, $connectionName, true);
        }

        generateScheduleLogs('info', 'CSV Generated' . json_encode($response));

        return $response;
    }
}
