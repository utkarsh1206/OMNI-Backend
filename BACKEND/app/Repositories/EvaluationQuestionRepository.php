<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EcdialEvaluationQuestion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationQuestionRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $query = EcdialEvaluationQuestion::query();

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $filters = [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', '')
            ];

            if (!empty($filters['search'])) {
                $query->where('question', 'like', "%" . $filters['search'] . "%");
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $query->with(['creator', 'updater']);

            $evaliationQuetions = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);


            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $evaliationQuetions,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question List Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $question = EcdialEvaluationQuestion::create([
                'question' => $request->question,
                'question_type' => $request->question_type,
                'answers' => $this->prepareAnswers($request->question_type, $request->answers),
                'status' => $request->status ?? 'active',
                'created_by' => $user->id
            ]);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $question,
                trans('message.question_create')
            );
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question Creation Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function find($evaliationQuetions)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $evaliationQuetions, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in fetching Evaluation Question: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $question = EcdialEvaluationQuestion::findOrFail($id);

            $updateData = [
                'updated_by' => $user->id
            ];

            if ($request->has('question')) {
                $updateData['question'] = $request->question;
            }

            if ($request->has('question_type')) {
                $updateData['question_type'] = $request->question_type;
                $updateData['answers'] = $this->prepareAnswers($request->question_type, $request->answers);
            } elseif ($request->has('answers')) {
                $updateData['answers'] = $request->answers;
            }

            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            $question->update($updateData);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $question, trans('message.question_update'));
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question Update Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $user = Auth::user();
            $question = EcdialEvaluationQuestion::findOrFail($id);
            $question->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), $question, trans('message.question_delete'));
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question Deletion Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function prepareAnswers($questionType, $answers)
    {
        if (in_array($questionType, ['MULTI', 'RADIO', 'DROPDOWN'])) {
            return $answers;
        }

        // If type is TEXT or anything else, answers must be null
        return null;
    }
}
