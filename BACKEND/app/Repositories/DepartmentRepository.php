<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\Department;
use Illuminate\Database\QueryException;


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
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Department::query();

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }

            if ($page === 'all') {
                $departments = $query->get();
                return $this->apiResponse->getResponseStructure(trans('message.success'), $departments, trans('message.fetch_data'));
            } else {
                $departments = $query->paginate($perPage);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $departments, trans('message.fetch_data'));
            }
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {

            $department = Department::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $department, trans('message.department_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
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
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
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
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
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
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
