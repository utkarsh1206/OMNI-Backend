<?php

namespace App\Repositories;

use App\Models\EmailLog;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class EmailLogRepository
{
    /**
     * Fetch email logs based on filters and pagination.
     */
    public function list($request)
    {
        $query = EmailLog::with('user', 'emailTemplate');

        if ($searchTerm = $request->input('search')) {
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('emailTemplate', fn($query) => $query->where('subject', 'like', "%$searchTerm%"))
                  ->orWhereHas('user', fn($query) => $query->where('username', 'like', "%$searchTerm%")
                                                            ->orWhere('email', 'like', "%$searchTerm%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('created_at', '=', Carbon::parse($date)->format('Y-m-d'));
        }

        return $request->input('page') === 'all'
            ? $query->get()
            : $query->paginate($request->input('per_page', 10));
    }

    /**
     * Find a specific email log by ID.
     */
    public function findById($id)
    {
        return EmailLog::findOrFail($id);
    }

    /**
     * Export email logs to CSV format.
     */
    public function exportToCsv($request)
    {
        $logsQuery = DB::table('email_logs')
            ->join('users', 'email_logs.user_id', '=', 'users.id')
            ->join('mail_template', 'email_logs.email_template_id', '=', 'mail_template.id')
            ->select('email_logs.id', 'email_logs.from_email', 'users.email as to_email', 'users.first_name as username', 'users.mobile as contact', 'mail_template.subject as subject', 'email_logs.created_at', 'email_logs.status');

        $logs = $logsQuery->get();
        $headers = ['From Email', 'To Email', 'Username', 'Contact', 'Subject', 'Date & Time', 'Status'];

        $callback = function () use ($logs, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->from_email, $log->to_email, $log->username, $log->contact,
                    $log->subject, $log->created_at, $log->status === 'sent' ? 'Sent' : 'Failed'
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="email_logs.csv"',
        ]);
    }
}
