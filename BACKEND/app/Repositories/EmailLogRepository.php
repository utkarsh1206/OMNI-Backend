<?php

namespace App\Repositories;

use App\Models\EmailLog;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Http\Response\CustomApiResponse;
use Illuminate\Database\QueryException;

class EmailLogRepository
{
    /**
     * Fetch email logs based on filters and pagination.
     */

    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }
    public function list($request)
    {
        try {
            // Validate required datetime filters
            if (!$request->has(['startDate', 'endDate'])) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            $query = EmailLog::with('user', 'emailTemplate');

            // Parse start and end datetimes
            $startDatetime = Carbon::parse($request->startDate);
            $endDatetime = Carbon::parse($request->endDate);

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

            if ($toEmail = $request->input('to_email')) {
                $query->whereRaw("JSON_EXTRACT(email_json, '$.to') LIKE ?", ["%$toEmail%"]);
            }

            // if ($date = $request->input('date')) {
            //     $query->whereDate('created_at', '=', Carbon::parse($date)->format('Y-m-d'));
            // }

            $query->whereBetween('created_at', [$startDatetime, $endDatetime])
                ->orderBy('id', 'desc');

            $email =  $request->input('page') === config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($request->input('per_page', config('omni.PER_PAGE')));
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email, trans('message.email_sent_success'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating Email  List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Find a specific email log by ID.
     */
    public function findById($id)
    {
        try {
            return EmailLog::findOrFail($id);
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating Email  List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Export email logs to CSV format.
     */
    public function exportToCsv($request)
    {
        $query = DB::table('email_logs')
            ->leftJoin('users', 'email_logs.user_id', '=', 'users.id')
            ->select(
                'email_logs.id',
                'email_logs.from_email',
                'email_logs.email_json',
                'email_logs.module_name',
                'email_logs.subject',
                'email_logs.body',
                'email_logs.status',
                'email_logs.created_at',
                'users.username',
                'users.email as user_email',
                'users.mobile as user_mobile'
            );
        // ->leftJoin('mail_template', 'email_logs.email_template_id', '=', 'mail_template.id')
        // ->select(
        //     'email_logs.from_email',
        //     'users.email as to_email',
        //     'users.first_name as username',
        //     'users.mobile as contact',
        //     'mail_template.subject as subject',
        //     'email_logs.created_at',
        //     'email_logs.status'
        // )

        if ($request->has('user_id') && $request->user_id) {
            $query->where('email_logs.user_id', $request->user_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('email_logs.status', $request->status);
        }

        if ($toEmail = $request->input('to_email')) {
            $query->whereRaw("JSON_EXTRACT(email_json, '$.to') LIKE ?", ["%$toEmail%"]);
        }

        // if ($request->has('date') && $request->date) {
        //     $query->whereDate('email_logs.created_at', $request->date);
        // }

        if ($request->filled('startDate') && $request->filled('endDate')) {
            $query->whereBetween('email_logs.created_at', [
                Carbon::parse($request->startDate)->startOfDay(),
                Carbon::parse($request->endDate)->endOfDay()
            ]);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email_logs.subject', 'like', "%$search%")
                    ->orWhere('email_logs.from_email', 'like', "%$search%")
                    ->orWhere('users.username', 'like', "%$search%");
            });
        }

        $logs = $query->orderBy('email_logs.id', 'desc')->get();

        // Define headers
        $headers = ['Username', 'From Email', 'To Email', 'Module', 'Email Subject', 'Created On', 'Status'];

        // Prepare rows
        $rows = $logs->map(function ($log) {
            $emailJson = json_decode($log->email_json, true);
            return [
                $log->username,
                $log->from_email,
                $emailJson['to'] ?? '-',
                $log->module_name,
                $log->subject,
                $log->created_at,
                $log->status,
            ];
        })->toArray();

        $fileUrl = exportCsv($headers, $rows, 'email_logs');

        // return response()->json([
        //     'success' => true,
        //     'file_url' => $fileUrl
        // ]);

        generateLogs("info", "test csv ");
        return $this->apiResponse->getResponseStructure(
            trans('message.success'),
            ['file_url' => $fileUrl],
            trans('message.report_generated')
        );
    }
}
