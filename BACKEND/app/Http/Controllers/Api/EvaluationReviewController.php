<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\EvaluationReviewRepository;
use Illuminate\Http\Request;

class EvaluationReviewController extends Controller
{
    protected $reviewRepository;

    public function __construct(EvaluationReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function store(Request $request, $type)
    {
        return $this->reviewRepository->storeReview($request->all(), $type);
    }

    public function getReview(Request $request, $type)
    {
        return $this->reviewRepository->getReview($request->all(), $type);
    }
}
