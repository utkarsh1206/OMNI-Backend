<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;

class UserGroupRepository
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
            $campaign_id = $request->input('campaign_id', '');
            $status = $request->input('status', '');

            // Start the query for tenants
            $query = UserGroup::query();

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orwhere('description', 'like', '%' . $searchTerm . '%');
                });
            }

            if (!empty($name)) {
                $query->where('name', $name);
            }
            if (!empty($campaign_id)) {
                $query->where('campaign_id', $campaign_id);
            }
            if (!empty($status)) {
                $query->where('status', $status);
            }
            // Paginate the results
            $perPage = $request->input('per_page', 10);
            $usergroup = $query->paginate($perPage);
            $page = $request->input('page', 1);

            if ($page === 'all') {
                // If 'page' is 'all', retrieve all records without pagination
                $usergroup = $query->get();
            } else {
                // Paginate the results
                $usergroup = $query->paginate($perPage);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.data_fetch'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(Request $request)
    {
        try {
            $usergroup = usergroup::create([
                'name' => $request->name,
                "department_id" => $request->department_id,
                "campaign_id" => $request->campaign_id,
                "description" => $request->description,
                "status" => $request->status
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.user_group_create_msg'));
        } catch (Exception $ex) {
            Log::error('Error during authentication: ' . $ex->getMessage());
            return response()->json(['error' => trans('message.wrong_with_query')], ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }


    public function update(Request $request, $usergroup)
    {
        try {
            $usergroup->update([
                'name' => $request->name,
                "campaign_id" => $request->campaign_id,
                "description" => $request->description,
                "department_id" => $request->department_id,
                // "tenant_id"=>1,//Make it dynamic
                "status" => $request->status
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.user_group_update_msg'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($usergroup)
    {
        try {
            $usergroup->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.user_group_delete_msg'));
        } catch (Exception $ex) {
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
