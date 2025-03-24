<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoteRequest;
use App\Models\Notes;
use App\Repositories\NoteRepository;
use Illuminate\Http\Request;

class NoteController extends Controller
{

    private $noteRepository;

    public function __construct(NoteRepository $noteRepository)
    {
        $this->noteRepository = $noteRepository;
    }
    /**
     * @OA\Get(
     *     path="/api/note",
     *     summary="Get notes for a support ticket",
     *     description="Fetches all notes associated with a given support ticket ID.",
     *     operationId="getNotes",
     *     tags={"Notes"},
     *
     *     @OA\Parameter(
     *         name="supportticket_id",
     *         in="query",
     *         description="The ID of the support ticket to retrieve notes for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="supportticket_id", type="integer", example=101),
     *                 @OA\Property(property="date", type="string", format="date-time", example="2024-03-03 12:00:00"),
     *                 @OA\Property(property="note", type="string", example="Follow up with customer."),
     *                 @OA\Property(property="created_by", type="integer", example=5)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    
    public function index(NoteRequest $request)
    {
        return  $this->noteRepository->index($request);
    }

    /**
     * @OA\Post(
     *     path="/api/note",
     *     summary="Create a new note",
     *     description="Creates a new note associated with a support ticket.",
     *     operationId="createNote",
     *     tags={"Notes"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supportticket_id", "note"},
     *             @OA\Property(property="supportticket_id", type="integer", example=101),
     *             @OA\Property(property="note", type="string", example="Resolved the customer issue."),
     *             @OA\Property(property="user_id", type="integer", example=5, description="User who created the note")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Note created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Note created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="supportticket_id", type="integer", example=101),
     *                 @OA\Property(property="date", type="string", format="date-time", example="2024-03-03 12:00:00"),
     *                 @OA\Property(property="note", type="string", example="Resolved the customer issue."),
     *                 @OA\Property(property="created_by", type="integer", example=5)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     */
    public function store(NoteRequest $request)
    {
        return  $this->noteRepository->store($request);
    }
}
