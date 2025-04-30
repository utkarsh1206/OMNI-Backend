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

    /**
     * @OA\Post(
     *     path="/api/evaluation/store-review",
     *     tags={"Evaluation"},
     *     summary="Store a new evaluation call review",
     *     security={{"bearerAuth": {}}},
     *     description="Stores a new evaluation for a given call, agent, and campaign.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="callId", type="integer", description="The ID of the call being reviewed."),
     *             @OA\Property(property="user_id", type="integer", description="The ID of the agent."),
     *             @OA\Property(property="campaign_id", type="integer", description="The ID of the campaign associated with the call."),
     *             @OA\Property(property="question_ids", type="string", description="Comma-separated list of question IDs included in the evaluation."),
     *             @OA\Property(property="selected_answers", type="string", description="Comma-separated list of selected answers for the questions."),
     *             @OA\Property(property="additional_comments", type="string", description="Additional comments provided by the reviewer."),
     *             @OA\Property(property="call_rating", type="integer", description="Rating of the call (1-5).")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully stored the review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object", description="The stored review data."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="error", type="string", example="Error message description")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        return $this->evaluationCallReviewRepository->storeReview($request);
    }

    /**
     * @OA\Get(
     *     path="/api/evaluation/get-review",
     *     tags={"Evaluation"},
     *     summary="Retrieve an evaluation review by user and call ID",
     *     security={{"bearerAuth": {}}},
     *     description="Retrieves the evaluation review for a specific agent and call.",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the agent."
     *     ),
     *     @OA\Parameter(
     *         name="call_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="The ID of the call for which the review is being retriewed."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the review",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object", description="The retrieved review data."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="error", type="string", example="Review not found for the given user and call ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error"),
     *             @OA\Property(property="error", type="string", example="Error message description")
     *         )
     *     )
     * )
     */
    public function getReview(Request $request)
    {
        return $this->evaluationCallReviewRepository->getReview($request);
    }
}
