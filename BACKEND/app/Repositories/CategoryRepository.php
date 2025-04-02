<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\Category;
use Exception;
class CategoryRepository
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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $name = $request->input('name');
            $department_id = $request->input('department_id');

            $query = Category::query();

            if ($name) {
                $query->where('name', $name);
            }
            if ($department_id) {
                $query->where('department_id', $department_id);
            }

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            $query =  $query->with('department');
            $category = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $category, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in generating Category Listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function store(Request $request)
    {
        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'department_id' => $request->department_id,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $category, trans('message.category_created_msg'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Storing Category: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => trans("message.category_not_found"),
                ], 404);
            }

            $category->update([
                'name' => $request->name,
                'description' => $request->description,
                'department_id' => $request->department_id,
            ]);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $category, trans('message.category_updated_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Updating Category: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'message' => trans("message.category_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $category->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $category, trans('message.category_deleted_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Deleting Category: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::with('department')->find($id);

            if (!$category) {
                return response()->json([
                    'message' => trans("message.category_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $category, trans('message.fetch_data'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Fetching Category: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
