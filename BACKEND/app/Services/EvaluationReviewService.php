<?php

namespace App\Services;

use App\Models\EcdialEvaluationQuestion;
use App\Models\EvaluationCallReview;
use App\Models\EvaluationTicketReview;
use App\Models\Users;
use Exception;

class EvaluationReviewService
{
    protected $typeMap = [
        'calls' => [
            'model' => \App\Models\EvaluationCallReview::class,
            'unique_id_field' => 'callerid',
        ],
        'support-ticket' => [
            'model' => \App\Models\EvaluationTicketReview::class,
            'unique_id_field' => 'ticket_id',
        ],
        // Add more types here
    ];

    public function getTypeMeta(string $type): array
    {
        $typeKey = strtolower($type);

        if (!isset($this->typeMap[$typeKey])) {
            throw new Exception("Unsupported evaluation type: {$type}");
        }

        return $this->typeMap[$typeKey];
    }

    public function getModelQuery(string $type)
    {
        return $this->getTypeMeta($type)['model']::query();
    }

    public function getModelInstance(string $type)
    {
        return new ($this->getTypeMeta($type)['model']);
    }

    public function getUniqueIdField(string $type): string
    {
        return $this->getTypeMeta($type)['unique_id_field'];
    }

    public function reviewExists($uniqueId, $userId, $type)
    {
        $model = $this->getModelQuery($type);

        $idField = $this->getUniqueIdField($type);

        return $model->where($idField, $uniqueId)
                     ->where('user_id', $userId)
                     ->exists();
    }

    public function storeReview($data, $type)
    {
        try {
            $loggedInUser = $data['loggedInUser'];
            $agent = Users::where('username', $data['user_id'])->value('id');

            if (!$agent) {
                throw new Exception('User not found.');
            }

            $processedData = $this->processQuestionsAndAnswers($data);

            $model = $this->getModelInstance($type);
            $idField = $this->getUniqueIdField($type);
            
            $recordData = [
                $idField => $data[$idField],
                'user_id' => $agent,
                'questions' => $processedData['questions'],
                'selected_answer' => $processedData['answers'],
                'comment' => $data['additional_comments'],
                'created_by' => $loggedInUser,
            ];
    
            // Dynamically add type-specific fields
            if (isset($data['campaign_id'])) {
                $recordData['campaign_id'] = $data['campaign_id'];
            }

            if (isset($data['call_rating'])) {
                $recordData['call_rating'] = $data['call_rating'];
            }

            if (isset($data['ticket_rating'])) {
                $recordData['ticket_rating'] = $data['ticket_rating'];
            }
    
            return $model->create($recordData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function processQuestionsAndAnswers($data)
    {
        $answers = $data['selected_answers'];
        $questionIds = array_keys($answers);

        $questionsText = EcdialEvaluationQuestion::where('status', 'active')
            ->whereIn('id', explode(',', $data['question_ids']))
            ->pluck('question', 'id')
            ->toArray();

        $questions = [];
        $allAnswers = [];

        foreach ($questionIds as $questionId) {
            if (isset($questionsText[$questionId])) {
                $questions[] = $questionsText[$questionId];
                $allAnswers[] = implode(',', $answers[$questionId]);
            }
        }

        // Combine questions with #$#$ separator
        $allQuestions = implode("#$#$", $questions);
        $allAnswersStr = implode("#$#$", $allAnswers);

        return [
            'questions' => $allQuestions,
            'answers' => $allAnswersStr
        ];
    }   
}
