<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use App\Models\IncidentType;
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
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = IncidentType::query();

            if ($name) {
                $query->where('name', $name);
            }
            if ($category_id) {
                $query->where('category_id', $category_id);
            }

            if (!empty($searchTerm)) {
                $query->where('name', 'like', "%$searchTerm%");
            }

            if (!empty($priority)) {
                $query->where('priority', $priority);  // Filter by priority
            }
            if (!empty($department_id)) {
                $query->where('$department_id', $department_id);  // Filter by priority
            }
            if (!empty($sla_id)) {
                $query->where('sla_id', $sla_id);  // Filter by priority
            }

            if ($page === 'all') {
                $incidentType = $query->with('category.department', 'sla_settings')->get();
                return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
            } else {
                $incidentType = $query->with('category.department', 'sla_settings')->paginate($perPage);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
            }
        } catch (QueryException $e) {
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
                'priority' => $request->priority,
                'sla_id' => $request->sla_id,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
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
                'priority' => $request->priority,
                'sla_id' => $request->sla_id,
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_update'));
        } catch (QueryException $e) {
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

            return response()->json([
                'message' => trans("message.incident_type_delete"),
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.incident_type_not_found'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $incidentType = IncidentType::with('category.department', 'sla_settings')->find($id);

            if (!$incidentType) {
                return response()->json([
                    'message' => trans("message.incident_type_not_found")
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $incidentType
            ]);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
        } catch (QueryException $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
