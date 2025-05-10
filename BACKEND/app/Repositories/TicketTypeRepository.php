<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\TicketType;
use App\Http\Response\CustomApiResponse;

use Exception;

class TicketTypeRepository
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
            $type = $request->input('type', '');
            $status = $request->input('status', '');
            
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = TicketType::query();

            if (!empty($searchTerm)) {
                $query->where('type', 'like', "%$searchTerm%")
                    ->orWhere('status', 'like', "%$searchTerm%");
            }

            if (!empty($type)) {
                $query->where('type', 'like', "%$type%");
            }
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $priorities = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priorities,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in TicketTypeRepository@list : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store($validatedData)
    {
        try {
            $ticketType = TicketType::create($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticketType,
                trans('message.ticket_type_created')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in TicketTypeRepository@store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($validatedData, TicketType $ticketType)
    {
        try {
            $ticketType->update($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticketType,
                trans('message.ticket_type_updated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in TicketTypeRepository@update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $ticketType = TicketType::findOrFail($id);

            $ticketType->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticketType,
                trans('message.ticket_type_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in TicketTypeRepository@delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $ticketType = TicketType::findOrFail($id);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $ticketType,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in TicketTypeRepository@show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
