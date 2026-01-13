<?php

namespace App\Repositories;

use Exception;
use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\EmailSchedule;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class EmailScheduleRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list($request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $reportName = $request->input('report_name', '');
            $frequency = $request->input('frequency', '');
            $subject = $request->input('subject', '');
            $fromEmail = $request->input('from_email', '');
            $toEmails = $request->input('to_emails', '');
            $scheduledTime = $request->input('scheduled_time', '');

            $query = EmailSchedule::select('*');

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('subject', 'like', '%' . $searchTerm . '%')
                        ->orWhere('from_email', 'like', '%' . $searchTerm . '%')
                        ->orWhere('body', 'like', '%' . $searchTerm . '%')
                        ->orWhere('scheduled_time', 'like', '%' . $searchTerm . '%')
                        ->orWhere('frequency', 'like', '%' . $searchTerm . '%')
                        ->orWhere('day_of_week', 'like', '%' . $searchTerm . '%')
                        ->orWhere('data_start_time', 'like', '%' . $searchTerm . '%')
                        ->orWhere('data_end_time', 'like', '%' . $searchTerm . '%')
                        ->orWhere('timezone', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($reportName) {
                $query->where('report_name', 'like', "%$reportName%");
            }

            if ($frequency) {
                $query->where('frequency', 'like', "%$frequency%");
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $email_schedule = $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_schedule, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Schedule List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $emailConfig = EmailSchedule::create($input);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $emailConfig, trans('message.email_schedule_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Schedule Store: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(Request $request, $email_schedule)
    {
        try {
            $update = $request->all();
            $email_schedule->update($update);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_schedule, trans('message.email_schedule_updated'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Schedule Update: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($email_schedule)
    {
        try {
            $email_schedule->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_schedule, trans('message.email_schedule_deleted'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Schedule Delete: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($email_schedule)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $email_schedule, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in generating Email Schedule Show: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
