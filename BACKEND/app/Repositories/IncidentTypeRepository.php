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
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
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

            $incidentType = $page == config('omni.ALL_PAGE') ? $query->with('category.department', 'slaSetting')->get() : $query->with('category.department', 'slaSetting')->paginate($perPage);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
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
                'priority' => $request->priority,
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
                'priority' => $request->priority,
                'sla_id' => $request->sla_id,
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $incidentType, trans('message.fetch_data'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Incident Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
