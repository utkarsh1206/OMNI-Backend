<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\Users;
use App\Services\EvaluationReviewService;
use Illuminate\Support\Facades\Auth;
use Exception;

class EvaluationReviewRepository
{
    protected $apiResponse;
    protected $reviewService;

    public function __construct(CustomApiResponse $apiResponse, EvaluationReviewService $reviewService)
    {
        $this->apiResponse = $apiResponse;
        $this->reviewService = $reviewService;
    }

    public function storeReview(array $data, string $type)
    {
        try {
            $data['loggedInUser'] = Auth::user()->username ?? null;

            $idField = $this->reviewService->getUniqueIdField($type);
            $userId = Users::where('username', $data['user_id'])->value('id');

            if ($this->reviewService->reviewExists($data[$idField], $userId, $type)) {
                return response()->json(['error' => "Review already exists for this {$type}."]);
            }

            $review = $this->reviewService->storeReview($data, $type);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $review,
                trans('message.review_update')
            );
        } catch (Exception $e) {
            generateLogs("error", "Store Review Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getReview(array $data, string $type)
    {
        try {
            // $userId = Users::where('username', $data['user_id'])->value('id');
            $userId = ($data['user_id'] === 'Unknown')
                ? 0
                : (Users::where('username', $data['user_id'])->value('id') ?? 0);

            $idField = $this->reviewService->getUniqueIdField($type);

            $review = $this->reviewService->getModelQuery($type)
                ->where('user_id', $userId)
                ->where($idField, $data[$idField])
                ->orderBy('id', 'desc')
                ->first();

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
            generateLogs("error", "Get Review Error: " . $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
