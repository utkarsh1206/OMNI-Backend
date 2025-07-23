<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EcdialEvaluationQuestion;
use App\Models\EvaluationQuestionUsageType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

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
                'status' => $request->input('status', ''),
                'usage_type' => $request->input('usage_type', ''),
            ];

            if (!empty($filters['search'])) {
                $query->where('question', 'like', "%" . $filters['search'] . "%");
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            // if (!empty($filters['usage_type'])) {
            //     $query->where('usage_type', 'like', "%" . $filters['usage_type'] . "%");
            // }

            if (!empty($filters['usage_type'])) {
                $usageTypeId = EvaluationQuestionUsageType::where('usage_type', $filters['usage_type'])->value('id');

                if ($usageTypeId) {
                    $query->whereRaw("FIND_IN_SET(?, usage_type)", [$usageTypeId]);
                } else {
                    $query->whereRaw('0 = 1');
                }
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
                'usage_type' => $this->usageTypeValid($request->usage_type),
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

    public function find($id)
    {
        try {
            $question = EcdialEvaluationQuestion::with(['creator', 'updater'])->find($id);

            if (!$question) {
                return response()->json([
                    'message' => trans("message.record_not_found"),
                ], ResponseHTTP::HTTP_NOT_FOUND);
            }

            $question->usage_type = explode(',', $question->usage_type ?? '');

            return $this->apiResponse->getResponseStructure(trans('message.success'), $question, trans('message.data_fetch'));
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

            if ($request->has('usage_type')) {
                $updateData['usage_type'] = $this->usageTypeValid($request->usage_type);
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

        return null;
    }

    private function usageTypeValid($usageType)
    {
        if (is_string($usageType)) {
            $usageType = explode(',', $usageType);
        }

        $validIds = EvaluationQuestionUsageType::whereIn('id', $usageType)
            ->pluck('id')
            ->toArray();
        return !empty($validIds) ? implode(',', $validIds) : null;
    }
}
