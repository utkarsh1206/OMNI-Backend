<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\EvaluationCallReviewRepository;
use Illuminate\Http\Request;

class EvaluationCallReviewController extends Controller
{
    protected $evaluationCallReviewRepository;

    public function __construct(EvaluationCallReviewRepository $evaluationCallReviewRepository)
    {
        $this->evaluationCallReviewRepository = $evaluationCallReviewRepository;
    }

    public function store(Request $request)
    {
        return $this->evaluationCallReviewRepository->storeReview($request);
    }
}
