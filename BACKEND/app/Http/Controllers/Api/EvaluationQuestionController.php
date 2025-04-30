<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluationQuestionRequest;
use App\Models\EcdialEvaluationQuestion;
use App\Repositories\EvaluationQuestionRepository;
use Illuminate\Http\Request;

class EvaluationQuestionController extends Controller
{
    protected $evaluationQuestionRepo;

    public function __construct(EvaluationQuestionRepository $evaluationQuestionRepo)
    {
        $this->evaluationQuestionRepo = $evaluationQuestionRepo;
    }

    // List all questions (with pagination)
    public function index(Request $request)
    {
        return $this->evaluationQuestionRepo->list($request);
    }

    // Store a new question
    public function store(EvaluationQuestionRequest $request)
    {
        return $this->evaluationQuestionRepo->store($request);
    }

    // Show specific question
    public function show(EcdialEvaluationQuestion $evaluationQuestion)
    {
        return $this->evaluationQuestionRepo->find($evaluationQuestion);
    }

    // Update a question
    public function update(EvaluationQuestionRequest $request, $id)
    {
        return $this->evaluationQuestionRepo->update($request, $id);    
    }

    // Delete a question (soft delete)
    public function destroy($id)
    {
        return $this->evaluationQuestionRepo->delete($id);    
    }
}
