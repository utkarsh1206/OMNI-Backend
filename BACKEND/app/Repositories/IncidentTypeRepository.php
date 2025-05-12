<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Category;
use App\Models\Department;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\IncidentType;
use App\Models\Priority;
use App\Models\SlaSetting;
use Illuminate\Database\QueryException;

class IncidentTypeRepository
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
            $priority = $request->input('priority', '');
            $department_id = $request->input('department_id');
            $category_id = $request->input('category_id');
            $sla_id = $request->input('sla_id');

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = IncidentType::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('priority', function ($q2) use ($searchTerm) {
                            $q2->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('category', function ($q3) use ($searchTerm) {
                            $q3->where('name', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('slaSetting', function ($q4) use ($searchTerm) {
                            $q4->where('name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }

            if ($name) {
                $query->where('name', 'like', "%$name%");
            }

            if ($category_id) {
                $categoryId = Category::where('name', 'like', "%$category_id%")->value('id');
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                } else {
                    $query->where('category_id', $category_id);
                }
            }

            $priorityId = null;
            if (!empty($priority)) {
                $priorityId = Priority::where('name', 'like', "%$priority%")->first()->id ?? null;
                if ($priorityId !== null) {
                    $query->where('priority_id', $priorityId);
                } else {
                    $query->where('priority_id', $priority);
                }
            }

            if ($department_id) {
                $departmentId = Department::where('name', 'like', "%$department_id%")->value('id');
                if ($departmentId) {
                    $query->whereHas('category.department', function ($q) use ($departmentId) {
                        $q->where('id', $departmentId);
                    });
                } else {
                    $query->where('department_id', $department_id);
                }
            }

            if (!empty($sla_id)) {
                $slaId = SlaSetting::where('name', 'like', "%$sla_id%")->value('id');
                if ($slaId) {
                    $query->where('id', $slaId);
                } else {
                    $query->where('sla_id', $sla_id);
                }
            }

            $incidentType = $page == config('omni.ALL_PAGE') ? $query->with('category.department', 'slaSetting.targets', 'priority:id,name')->get() : $query->with('category.department', 'slaSetting.targets', 'priority:id,name')->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.data_fetch'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident type : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $incidentType = IncidentType::create([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'priority_id' => $request->priority,
                'sla_id' => $request->sla_id,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $incidentType = IncidentType::find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => trans("message.incident_type_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $incidentType->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'priority_id' => $request->priority,
                'sla_id' => $request->sla_id,
                'department_id' => $request->department_id
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_update'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($id)
    {
        try {
            $incidentType = IncidentType::find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => trans("message.incident_type_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $incidentType->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_delete'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $incidentType = IncidentType::with('category.department', 'slaSetting')->find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => trans("message.incident_type_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.data_fetch'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
