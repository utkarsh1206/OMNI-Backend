<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Repositories\RoleRepository;
use App\Http\Response\CustomApiResponse;
use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     operationId="getRoles",
     *     tags={"Roles"},
     *     summary="Get list of roles",
     *     description="Returns list of roles with permissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function index(Request $request)
    {
        return $this->roleRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     operationId="createRole",
     *     tags={"Roles"},
     *     summary="Create a new role",
     *     description="Creates a role with actions and tenant modules",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "permissions"},
     *             @OA\Property(property="name", type="string", example="Admin"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="tenant_module_id", type="integer", example=1),
     *                     @OA\Property(property="action_ids", type="array", @OA\Items(type="integer"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Role created successfully"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function store(RoleRequest $request)
    {
        return $this->roleRepository->create($request);
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     operationId="getRoleById",
     *     tags={"Roles"},
     *     summary="Get role details",
     *     description="Returns a single role with associated actions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Successful operation"),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function show(Role $role)
    {
        return $this->roleRepository->show($role);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     operationId="updateRole",
     *     tags={"Roles"},
     *     summary="Update role",
     *     description="Updates role and its permissions",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "permissions"},
     *             @OA\Property(property="name", type="string", example="Updated Role"),
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="tenant_module_id", type="integer"),
     *                     @OA\Property(property="action_ids", type="array", @OA\Items(type="integer"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role updated successfully"),
     *     @OA\Response(response=422, description="Validation Error"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function update(RoleRequest $request, Role $role)
    {
        return $this->roleRepository->update($request, $role);
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     operationId="deleteRole",
     *     tags={"Roles"},
     *     summary="Delete role",
     *     description="Deletes the specified role",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Role deleted successfully"),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=500, description="Server Error")
     * )
     */
    public function destroy(Role $role)
    {
        return $this->roleRepository->delete($role);
    }
}
