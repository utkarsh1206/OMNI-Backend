<?php

namespace App\Repositories;

use App\Models\AssignmentRule;
use App\Http\Response\CustomApiResponse;
use Exception;

class AssignmentRuleRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list($request)
    {
        // Log entry point and request parameters
        generateLogs("info", "Fetching assignment rules with filters: department_id=" . ($request->department_id ?? 'null') . ", incident_type_id=" . ($request->incident_type_id ?? 'null'));

        try {
            $rules = AssignmentRule::with(['department', 'incidentType', 'sla'])
                ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
                ->when($request->incident_type_id, fn($q) => $q->where('incident_type_id', $request->incident_type_id))
                ->paginate(20);

            // Log successful data retrieval
            generateLogs("info", "Successfully fetched " . $rules->total() . " assignment rules.");

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rules, trans('message.data_fetch'));
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error fetching assignment rules.", $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function create(array $data)
    {
        // Log entry point and data payload
        generateLogs("info", "Attempting to create new assignment rule with data: " . json_encode($data));

        try {
            $rule = AssignmentRule::create($data);

            // Log successful creation
            generateLogs("info", "Assignment rule created successfully with ID: " . $rule->id);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rule, trans('message.assignment_rule_created'));
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error creating assignment rule.", $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function find($id)
    {
        // Log entry point
        generateLogs("info", "Attempting to find assignment rule with ID: " . $id);

        try {
            $rule = AssignmentRule::with(['department', 'incidentType', 'sla'])->findOrFail($id);

            // Log successful retrieval
            generateLogs("info", "Assignment rule found with ID: " . $id);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rule, trans('message.data_fetch'));
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error finding assignment rule with ID: " . $id, $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update($id, array $data)
    {
        // Log entry point and data payload
        generateLogs("info", "Attempting to update assignment rule ID: " . $id . " with data: " . json_encode($data));

        try {
            $rule = AssignmentRule::findOrFail($id);
            $rule->update($data);

            // Log successful update
            generateLogs("info", "Assignment rule ID: " . $id . " updated successfully.");

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rule, 'Rule updated successfully.');
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error updating assignment rule ID: " . $id, $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function delete($id)
    {
        // Log entry point
        generateLogs("info", "Attempting to delete assignment rule with ID: " . $id);

        try {
            $rule = AssignmentRule::findOrFail($id);
            $rule->delete();

            // Log successful deletion
            generateLogs("info", "Assignment rule ID: " . $id . " deleted successfully.");

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rule, 'Rule deleted successfully.');
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error deleting assignment rule ID: " . $id, $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function toggleStatus($id)
    {
        // Log entry point
        generateLogs("info", "Attempting to toggle status for assignment rule ID: " . $id);

        try {
            $rule = AssignmentRule::findOrFail($id);
            $rule->enabled = !$rule->enabled;
            $rule->save();

            // Log new status
            generateLogs("info", "Assignment rule ID: " . $id . " status toggled to: " . ($rule->enabled ? 'enabled' : 'disabled'));

            return $this->apiResponse->getResponseStructure(trans('message.success'), $rule, 'Rule status updated.');
        } catch (Exception $ex) {
            // Log error with exception message
            generateLogs("error", "Error toggling status for assignment rule ID: " . $id, $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
