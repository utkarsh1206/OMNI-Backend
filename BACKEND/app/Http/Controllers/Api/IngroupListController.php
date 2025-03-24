<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcdialInboundGroup;
use App\Repositories\InGroupListRepository;

class IngroupListController extends Controller
{

    private $inGroupListRepository;

    public function __construct(InGroupListRepository $inGroupListRepository)
    {
        $this->inGroupListRepository = $inGroupListRepository;
    }
    /**
     * @OA\Get(
     *     path="/api/ingroups",
     *     summary="Get a list of inbound groups",
     *     description="Fetches a list of inbound groups ordered by group_id.",
     *     operationId="getInboundGroups",
     *     tags={"Inbound Groups"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="group_id", type="string", example="IG123"),
     *                 @OA\Property(property="group_name", type="string", example="Support Group")
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
        return $this->inGroupListRepository->list();
    }
}
