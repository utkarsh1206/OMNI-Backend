<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AssignmentRuleRepository;
use App\Http\Requests\AssignmentRuleRequest;

class AssignmentRuleController extends Controller
{
    private $assignmentRuleRepository;

    public function __construct(AssignmentRuleRepository $assignmentRuleRepository)
    {
        $this->assignmentRuleRepository = $assignmentRuleRepository;
    }

    public function index(Request $request)
    {
        return $this->assignmentRuleRepository->list($request);
    }

    public function store(AssignmentRuleRequest $request)
    {
        generateLogs("info", "request " . json_encode($request->all()));
        return $this->assignmentRuleRepository->create($request->validated());
    }

    public function show($id)
    {
        return $this->assignmentRuleRepository->find($id);
    }

    public function update(AssignmentRuleRequest $request, $id)
    {
        return $this->assignmentRuleRepository->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->assignmentRuleRepository->delete($id);
    }

    public function toggleStatus($id)
    {
        return $this->assignmentRuleRepository->toggleStatus($id);
    }
}
