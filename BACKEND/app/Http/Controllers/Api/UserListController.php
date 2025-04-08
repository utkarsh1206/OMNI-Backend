<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserListRepository;

class UserListController extends Controller
{
    private $userListRepository;

    public function __construct(UserListRepository $userListRepository)
    {
        $this->userListRepository = $userListRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/usersList",
     *     summary="Get a list of users",
     *     description="Fetches a list of users with a user level less than 8, ordered by username.",
     *     operationId="getUsers",
     *     tags={"Users"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="user", type="string", example="john_doe")
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
    public function index()
    {
        return $this->userListRepository->list();
    }
}
