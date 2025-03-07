<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\EmailLog;
use App\Http\Response\CustomApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailLogRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
         // Get the search term from the request
         $searchTerm = $request->input('search', '');
         $status = $request->input('status', '');
         $userId = $request->input('user_id', '');
         $date = $request->input('date', ''); // Single date filter
 
         $query = EmailLog::with('user', 'email_template');
 
         // Apply search filter for email template subject and user details
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('email_template', function ($query) use ($searchTerm) {
                    $query->where('subject', 'like', "%$searchTerm%");
                })
                ->orWhereHas('user', function ($query) use ($searchTerm) {
                    $query->where('username', 'like', "%$searchTerm%")
                        ->orWhere('email', 'like', "%$searchTerm%");
                });
            });
        }
 
         // Apply status filter if provided
        if (!empty($status)) {
            $query->where('status', $status);
        }
 
         // Apply user filter if provided
        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }
 
         // Apply date filter if provided
        if (!empty($date)) {
            $query->whereDate('created_at', '=', Carbon::parse($date)->format('Y-m-d'));
        }
 
         // Paginate the results
         $perPage = $request->input('per_page', 10);
         $page = $request->input('page', 1);
 
        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $result = $query->get();
            return response()->json([
                'data' => $result,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $result, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $result = $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $result, trans('message.data_fetch'));
        }
    }

    public function exportCsv()
    {
         // Modify query to select from both email_logs and users
         $logsQuery = DB::table('email_logs')
         ->join('users', 'email_logs.user_id', '=', 'users.id')  // Join with users table
         ->join('mail_template', 'email_logs.email_template_id', '=', 'mail_template.id')
         ->select(
             'email_logs.id',
             'email_logs.from_email',
             'users.email as to_email',  // Selecting the email from users table as to_email
             'users.first_name as username',
             'users.mobile as contact',
             'mail_template.subject as subject',
             'email_logs.created_at',
             'email_logs.status'
         );

     // Fetch the logs
        $logs = $logsQuery->get();

     // Simplified headers, now including "To Email"
        $headers = ['From Email', 'To Email','Username','Contact','subject', 'Date & Time', 'Status'];

        $callback = function () use ($headers, $logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($logs as $log) {
                $row = [
                 $log->from_email,
                 $log->to_email,  // Displaying to_email from users table
                 $log->username,
                 $log->contact,
                 $log->subject,
                 $log->created_at,
                 $log->status === 'sent' ? 'Sent' : 'Failed',
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        $response = new StreamedResponse($callback, 200, [
         'Content-Type' => 'text/csv',
         'Content-Disposition' => 'attachment; filename="email_logs.csv"',
        ]);

        return $response;
    }

    public function show($id)
    {
        $info =  EmailLog::find($id);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $info, trans('message.data_fetch'));
    }
}
