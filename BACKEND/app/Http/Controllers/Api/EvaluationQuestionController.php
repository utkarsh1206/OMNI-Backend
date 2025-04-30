<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluationQuestionRequest;
use App\Models\EcdialEvaluationQuestion;
use App\Repositories\EvaluationQuestionRepository;
use Illuminate\Http\Request;

class EvaluationQuestionController extends Controller
{
    protected $evaluationQuestionRepo;

    public function __construct(EvaluationQuestionRepository $evaluationQuestionRepo)
    {
        $this->evaluationQuestionRepo = $evaluationQuestionRepo;
    }

    /**
     * @OA\Get(
     *     path="/api/evaluation/questions",
     *     tags={"Evaluation"},
     *     summary="Get a list of evaluation questions",
     *     security={{"bearerAuth": {}}},
     *     description="Returns a paginated or full list of evaluation questions based on filters",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search query for the question text"
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by question status (e.g. 'active', 'inactive')"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         description="Number of results per page"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1),
     *         description="Page number"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the list of evaluation questions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully!"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="status", type="string", example="Data fetch successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    // List all questions (with pagination)
    public function index(Request $request)
    {
        return $this->evaluationQuestionRepo->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/evaluation/questions",
     *     tags={"Evaluation"},
     *     summary="Create a new evaluation question",
     *     security={{"bearerAuth": {}}},
     *     description="Stores a new evaluation question with specified details.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question", type="string", description="The text of the evaluation question"),
     *             @OA\Property(property="question_type", type="string", description="The type of question (e.g., MULTI, RADIO, TEXT)"),
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string"), description="List of possible answers for MULTI, RADIO, or DROPDOWN types"),
     *             @OA\Property(property="status", type="string", description="Status of the question (default is 'active')")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created a new evaluation question",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question created successfully!"),
     *             @OA\Property(property="data", type="object", description="The created question data"),
     *             @OA\Property(property="status", type="string", example="Question creation successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    // Store a new question
    public function store(EvaluationQuestionRequest $request)
    {
        return $this->evaluationQuestionRepo->store($request);
    }

    /**
     * @OA\Get(
     *     path="/api/evaluation/questions/{id}",
     *     tags={"Evaluation"},
     *     summary="Find a specific evaluation question",
     *     security={{"bearerAuth": {}}},
     *     description="Retrieves a specific evaluation question by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the evaluation question"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the evaluation question",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data has been fetched successfully!"),
     *             @OA\Property(property="data", type="object", description="The evaluation question data"),
     *             @OA\Property(property="status", type="string", example="Data fetch successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evaluation question not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="error", type="string", example="Evaluation question not found with this ID")
     *         )
     *     )
     * )
     */
    // Show specific question
    public function show($id)
    {
        return $this->evaluationQuestionRepo->find($id);
    }

    /**
     * @OA\Put(
     *     path="/api/evaluation/questions/{id}",
     *     tags={"Evaluation"},
     *     summary="Update an existing evaluation question",
     *     security={{"bearerAuth": {}}},
     *     description="Update an evaluation question by its ID with new data.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the evaluation question to be updated"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question", type="string", description="The text of the evaluation question"),
     *             @OA\Property(property="question_type", type="string", description="The type of question (e.g., MULTI, RADIO, TEXT)"),
     *             @OA\Property(property="answers", type="array", @OA\Items(type="string"), description="List of possible answers for MULTI, RADIO, or DROPDOWN types"),
     *             @OA\Property(property="status", type="string", description="Status of the question")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated the evaluation question",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question updated successfully!"),
     *             @OA\Property(property="data", type="object", description="The updated question data"),
     *             @OA\Property(property="status", type="string", example="Question updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evaluation question not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="error", type="string", example="Evaluation question not found with this ID")
     *         )
     *     )
     * )
     */
    // Update a question
    public function update(EvaluationQuestionRequest $request, $id)
    {
        return $this->evaluationQuestionRepo->update($request, $id);    
    }

    /**
     * @OA\Delete(
     *     path="/api/evaluation/questions/{id}",
     *     tags={"Evaluation"},
     *     summary="Delete an evaluation question",
     *     security={{"bearerAuth": {}}},
     *     description="Deletes an evaluation question by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the evaluation question to be deleted"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted the evaluation question",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Question deleted successfully!"),
     *             @OA\Property(property="status", type="string", example="Question deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evaluation question not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found"),
     *             @OA\Property(property="error", type="string", example="Evaluation question not found with this ID")
     *         )
     *     )
     * )
     */
    // Delete a question (soft delete)
    public function destroy($id)
    {
        return $this->evaluationQuestionRepo->delete($id);    
    }
}
