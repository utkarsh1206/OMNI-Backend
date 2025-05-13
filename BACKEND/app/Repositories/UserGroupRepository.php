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

            // Advanced filters
            $filters = [
                'name' => $request->input('name'),
                'department_id' => $request->input('department_id'),
                'status' => $request->input('status'),
            ];

            // Start query with optional join if needed for department name search
            $query = UserGroup::query();

            // OPTIONAL JOIN for live search on department name
            // $query->leftJoin('departments', 'user_groups.department_id', '=', 'departments.id')
            //       ->select('user_groups.*');

            // ✅ LIVE SEARCH — across name, description (and optionally department name)
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%');
                    // ->orWhere('departments.name', 'like', '%' . $searchTerm . '%'); // if joined
                });
            }

            // ✅ ADVANCED FILTERS
            if (!empty($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // ✅ PAGINATION
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $usergroups = $page == config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $usergroups,
                trans('message.data_fetch')
            );
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
