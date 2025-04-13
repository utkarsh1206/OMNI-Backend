<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\UserGroup;
use App\Http\Response\CustomApiResponse;

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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            $usergroup =   $page === config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during User Group Listing : ', $ex->getMessage());
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
            generateLogs('error', 'Error during User Group Create : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
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
            generateLogs('error', 'Error during User Group Update : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
    
    public function show(UserGroup $usergroup)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error fetching User Group : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
    
    public function delete($usergroup)
    {
        try {
            $usergroup->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.user_group_delete_msg'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error during User Group Delete : ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
