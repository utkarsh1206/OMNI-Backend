<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Priority;
use App\Http\Response\CustomApiResponse;

use Exception;

class PriorityRepository
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

            $query = Priority::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%")
                    ->orWhere('status', 'like', "%$searchTerm%");
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }
            if (!empty($status)) {
                $query->where('status', $status);
            }

            $priorities = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priorities,
                trans('message.fetch_data')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in PriorityRepository@list : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store($validatedData)
    {
        try {
            $priority = Priority::create($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priority,
                trans('message.priority_created')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Priority Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($validatedData, Priority $priority)
    {
        try {
            $priority->update($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priority,
                trans('message.priority_updated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Priority Update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $priority = Priority::findOrFail($id);

            $priority->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priority,
                trans('message.priority_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Priority Delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $priority = Priority::findOrFail($id);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $priority,
                trans('message.fetch_data')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Priority Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
