<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserGroupRequest;
use Illuminate\Http\Request;
use App\Models\Usergroup;
use App\Repositories\UserGroupRepository;

class UserGroupController extends Controller
{
    private $userGroupRepository;

    public function __construct(UserGroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * @OA\Get(
     *     path="/usergroup",
     *     summary="Get list of user groups",
     *     description="Fetches a paginated list of user groups with optional filters.",
     *     operationId="getUserGroups",
     *     tags={"User Groups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="search", in="query", required=false, description="Search keyword", @OA\Schema(type="string")),
     *     @OA\Parameter(name="name", in="query", required=false, description="Filter by group name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="campaign_id", in="query", required=false, description="Filter by campaign ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by status", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Results per page", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, description="Page number", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of user groups fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Sales Group"),
     *                 @OA\Property(property="department_id", type="integer", example=3),
     *                 @OA\Property(property="campaign_id", type="integer", example=101),
     *                 @OA\Property(property="description", type="string", example="Handles sales-related tickets"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->userGroupRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/usergroup",
     *     summary="Create a new user group",
     *     description="Creates a new user group with the provided data.",
     *     operationId="createUserGroup",
     *     tags={"User Groups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "department_id", "campaign_id", "status"},
     *             @OA\Property(property="name", type="string", example="Sales Group"),
     *             @OA\Property(property="department_id", type="integer", example=3),
     *             @OA\Property(property="campaign_id", type="integer", example=101),
     *             @OA\Property(property="description", type="string", example="Handles sales-related tickets"),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User group created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User group created successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Sales Group"),
     *                 @OA\Property(property="department_id", type="integer", example=3),
     *                 @OA\Property(property="campaign_id", type="integer", example=101),
     *                 @OA\Property(property="description", type="string", example="Handles sales-related tickets"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong"
     *             )
     *         )
     *     )
     * )
     */
    public function store(UserGroupRequest $request)
    {
        return $this->userGroupRepository->create($request);
    }

    /**
     * @OA\Get(
     *     path="/usergroup/{id}",
     *     summary="Get a single user group",
     *     description="Returns the details of a specific user group.",
     *     operationId="getUserGroupById",
     *     tags={"User Groups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User group details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Sales Group"),
     *                 @OA\Property(property="department_id", type="integer", example=3),
     *                 @OA\Property(property="campaign_id", type="integer", example=101),
     *                 @OA\Property(property="description", type="string", example="Group description here"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function show(Usergroup $usergroup)
    {
        return $this->userGroupRepository->show($usergroup);
    }

    /**
     * @OA\Put(
     *     path="/usergroup/{id}",
     *     summary="Update an existing user group",
     *     description="Updates a user group using the provided data.",
     *     operationId="updateUserGroup",
     *     tags={"User Groups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "department_id", "campaign_id", "status"},
     *             @OA\Property(property="name", type="string", example="Updated Group"),
     *             @OA\Property(property="department_id", type="integer", example=2),
     *             @OA\Property(property="campaign_id", type="integer", example=102),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="status", type="string", example="inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User group updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User group updated successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Group"),
     *                 @OA\Property(property="department_id", type="integer", example=2),
     *                 @OA\Property(property="campaign_id", type="integer", example=102),
     *                 @OA\Property(property="description", type="string", example="Updated description"),
     *                 @OA\Property(property="status", type="string", example="inactive"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Usergroup $usergroup)
    {
        return $this->userGroupRepository->update($request, $usergroup);
    }

    /**
     * @OA\Delete(
     *     path="/usergroup/{id}",
     *     summary="Delete a user group",
     *     description="Deletes the specified user group.",
     *     operationId="deleteUserGroup",
     *     tags={"User Groups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User Group ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User group deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User group deleted successfully"),
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Deleted Group")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input parameters",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid parameters")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function destroy(Usergroup $usergroup)
    {
        return $this->userGroupRepository->delete($usergroup);
    }
}
