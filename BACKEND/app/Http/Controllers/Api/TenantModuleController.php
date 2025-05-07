<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\TenantModuleRepository;

class TenantModuleController extends Controller
{
    private $tenantModuleRepository;

    public function __construct(TenantModuleRepository $tenantModuleRepository)
    {
        $this->tenantModuleRepository = $tenantModuleRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/tenant-modules",
     *     summary="Retrieve a list of tenant modules",
     *     description="Returns a list of all tenant modules.",
     *     operationId="listTenantModules",
     *     tags={"Tenant Modules"},
     *     @OA\Response(
     *         response=200,
     *         description="List of tenant modules",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Leads"),
     *                     @OA\Property(property="status", type="string", example="active")
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
        return $this->tenantModuleRepository->list();
    }
}
