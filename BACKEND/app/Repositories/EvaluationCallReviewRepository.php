<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EcdialEvaluationQuestion;
use App\Models\EvaluationCallReview;
use App\Models\Users;
use Exception;
use Illuminate\Support\Facades\Auth;

class EvaluationCallReviewRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function storeReview($data)
    {
        try {
            $callId = $data['callId'];
            $agent = $data['user_id'];
            $campaign = $data['campaign_id'];
            $additionalComments = $data['additional_comments'];
            $callRating = $data['call_rating'];

            $loggedInUser = Auth::user()->username ?? null;

            $questionsText = EcdialEvaluationQuestion::where('status', 'active')
                ->whereIn('id', explode(',', $data['question_ids']))
                ->pluck('question', 'id')
                ->toArray();

            // Process questions
            $questionIds = explode(',', $data['question_ids']);
            $questions = [];


            foreach ($questionIds as $questionId) {
                if (isset($questionsText[$questionId])) {
                    $questions[] = $questionsText[$questionId];
                }
            }

            // Combine questions with #$#$ separator
            $allQuestions = implode("#$#$", $questions);

            // For answers, just replace commas with #$#$
            $allAnswers = isset($data['selected_answers'])
                ? str_replace(",", "#$#$", $data['selected_answers'])
                : '';

            //Insert into database
            $review = EvaluationCallReview::create([
                'callerid' => $callId,
                'user_id' => $agent,
                'campaign_id' => $campaign,
                'questions' => $allQuestions,
                'selected_answer' => $allAnswers,
                'comment' => $additionalComments,
                'call_rating' => $callRating ?? '0',
                'created_by' => $loggedInUser,
            ]);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $review,
                trans('message.review_update')
            );
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question List Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Get review based on user and call ID.
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReview($data)
    {
        try {
            $userName = $data['user_id'];
            $callId = $data['call_id'];
      
            $userId = Users::where('username', $userName)->value('id');

            // Retrieve the review data for the given user and call ID
            $review = EvaluationCallReview::where('user_id', $userId)
                ->where('callerid', $callId)
                ->orderBy('id', 'desc')
                ->first();

            // Check if review exists
            if (!$review) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.record_not_found')
                );
            }

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $review,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs("error", "Error retrieving review: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
