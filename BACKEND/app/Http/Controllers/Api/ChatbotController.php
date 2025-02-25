<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatbot;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class ChatbotController extends Controller
{
    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $question = $request->input('question', '');
            $answer = $request->input('answer', '');
            $keywords = $request->input('keywords', '');
            $status = $request->input('status', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Chatbot::query();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('question', 'like', "%{$searchTerm}%")
                      ->orWhere('answer', 'like', "%{$searchTerm}%")
                      ->orWhereJsonContains('keywords', $searchTerm);
                });
            }

            if (!empty($question)) {
                $query->where('question', 'like', "%{$question}%");
            }

            if (!empty($answer)) {
                $query->where('answer', 'like', "%{$answer}%");
            }

            if (!empty($keywords)) {
                $query->whereJsonContains('keywords', $keywords);
            }

            if ($status !== '') {
                $query->where('status', $status === 'true');
            }

            if ($page === 'all') {
                $chatbots = $query->get();
                return response()->json([
                    'data' => $chatbots,
                ]);
            }

            $chatbots = $query->paginate($perPage);
            return response()->json([
                'meta' => [
                    'current_page' => $chatbots->currentPage(),
                    'last_page' => $chatbots->lastPage(),
                    'per_page' => $chatbots->perPage(),
                    'total' => $chatbots->total(),
                ],
                'data' => $chatbots->items(),
            ]);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching chatbots: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $request->merge(['status' => $status]);

            $validator = Validator::make($request->all(), [
                'question' => 'required|string|unique:chatbots,question',
                'answer' => 'required|string',
                'status' => 'required|boolean',
                'keywords' => 'nullable|array|max:5',
                'keywords.*' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $chatbot = Chatbot::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
                'keywords' => $request->keywords ?? [],
            ]);

            return response()->json([
                'message' => 'Chatbot created successfully!',
                'data' => $chatbot
            ], 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error creating chatbot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $chatbot = Chatbot::find($id);

            if (!$chatbot) {
                return response()->json([
                    'message' => 'Chatbot not found.'
                ], 404);
            }

            return response()->json([
                'data' => $chatbot
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error fetching chatbot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $chatbot = Chatbot::find($id);

            if (!$chatbot) {
                return response()->json([
                    'message' => 'Chatbot not found.'
                ], 404);
            }

            $status = filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $request->merge(['status' => $status]);

            $validator = Validator::make($request->all(), [
                'question' => 'required|string|unique:chatbots,question,' . $chatbot->id,
                'answer' => 'required|string',
                'status' => 'required|boolean',
                'keywords' => 'nullable|array|max:5',
                'keywords.*' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $chatbot->update([
                'question' => $request->question,
                'answer' => $request->answer,
                'status' => $request->status,
                'keywords' => $request->keywords ?? [],
            ]);

            return response()->json([
                'message' => 'Chatbot updated successfully!',
                'data' => $chatbot
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error updating chatbot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $chatbot = Chatbot::find($id);

            if (!$chatbot) {
                return response()->json([
                    'message' => 'Chatbot not found.'
                ], 404);
            }

            $chatbot->delete();

            return response()->json([
                'message' => 'Chatbot deleted successfully',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Error deleting chatbot: ' . $e->getMessage()
            ], 500);
        }
    }
}