<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\SupportTicketStatus;
use Exception;

class SupportTicketStatusRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $name = $request->input('name', '');
            $status = $request->input('status', '');

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = SupportTicketStatus::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%")
                    ->orWhere('status', 'like', "%$searchTerm%");
            }

            if (!empty($name)) {
                $query->where('name', 'like', "%$name%");
            }
            if (!empty($status)) {
                $query->where('status', 'like',  $status);
            }

            $statuses = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $statuses,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in StatusRepository@list : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store($validatedData)
    {
        try {
            $status = SupportTicketStatus::create($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $status,
                trans('message.status_created')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in StatusRepository@Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $status = SupportTicketStatus::findOrFail($id);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $status,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in StatusRepository@Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($validatedData, SupportTicketStatus $ticket_status)
    {
        try {
            $ticket_status->update($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticket_status,
                trans('message.status_updated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in StatusRepository@Update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $status = SupportTicketStatus::findOrFail($id);
            
            $status->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $status,
                trans('message.status_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in StatusRepository@Delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}