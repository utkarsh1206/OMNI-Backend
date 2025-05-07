<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ActionRepository;

class ActionController extends Controller
{
    private $actionRepository;

    public function __construct(ActionRepository $actionRepository)
    {
        $this->actionRepository = $actionRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/actions",
     *     summary="Retrieve a list of actions",
     *     description="Returns a list of all actions.",
     *     operationId="listActions",
     *     tags={"Actions"},
     *     @OA\Response(
     *         response=200,
     *         description="List of actions",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="view"),
     *                     @OA\Property(property="description", type="string", example="View records")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->actionRepository->list();
    }
}
