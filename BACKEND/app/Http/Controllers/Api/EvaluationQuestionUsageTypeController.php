<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluationQuestionUsageTypeRequest;
use App\Models\EvaluationQuestionUsageType;
use App\Repositories\EvaluationQuestionUsageTypeRepository;
use Illuminate\Http\Request;

class EvaluationQuestionUsageTypeController extends Controller
{
    private $usageTypeRepo;

    public function __construct(EvaluationQuestionUsageTypeRepository $usageTypeRepo)
    {
        $this->usageTypeRepo = $usageTypeRepo;
    }

    public function index(Request $request)
    {
        return $this->usageTypeRepo->list($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EvaluationQuestionUsageTypeRequest $request)
    {
        return $this->usageTypeRepo->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return $this->usageTypeRepo->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EvaluationQuestionUsageTypeRequest $request, EvaluationQuestionUsageType $question_usage_type)
    {
        return $this->usageTypeRepo->update($request->validated(), $question_usage_type);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->usageTypeRepo->delete($id);
    }
}
