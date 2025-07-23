<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\EvaluationQuestionUsageType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class EvaluationQuestionUsageTypeRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $usageType = $request->input('usage_type');
            $status = $request->input('status', '');

            $query = EvaluationQuestionUsageType::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('usage_type', 'like', "%$searchTerm%")
                        ->orWhere('status', 'like', "%$searchTerm%");
                });
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if (!empty($status)) {
                $query->where('status', $status);
            }

            if (!empty($usageType)) {
                $query->where('usage_type', 'like', "%$usageType%");
            }

            $quetionsUsageType = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $quetionsUsageType,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs("error", "Evaluation Question Usage Type List Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store($validatedData)
    {
        try {
            $usageType = EvaluationQuestionUsageType::create($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $usageType,
                trans('message.question_usage_type_created')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Question Usage type Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function update($validatedData, EvaluationQuestionUsageType $usageType)
    {
        try {
            $usageType->update($validatedData);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $usageType,
                trans('message.question_usage_type_updated')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Question Usage type Update : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function delete($id)
    {
        try {
            $usageType = EvaluationQuestionUsageType::findOrFail($id);

            $usageType->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $usageType,
                trans('message.question_usage_type_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Question Usage type Delete : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($id)
    {
        try {
            $usageType = EvaluationQuestionUsageType::findOrFail($id);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $usageType,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Question Usage type Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
