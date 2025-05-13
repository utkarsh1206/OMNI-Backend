<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\Department;
use Exception;

class DepartmentRepository
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = Department::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%")
                    ->orWhere('description', 'like', '%' . $searchTerm . '%');
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }
            $departments = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $departments, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Department Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $department, trans('message.department_created'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in saving Department: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' =>  trans("message.department_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $department->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $department, trans('message.department_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Department: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' => trans("message.department_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $department->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $department, trans('message.department_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Department: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show($id)
    {
        try {
            $department = Department::find($id);

            if (!$department) {
                return response()->json([
                    'message' => trans("message.department_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $department, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Department: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
