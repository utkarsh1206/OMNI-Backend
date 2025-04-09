<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use Illuminate\Http\Request;
use App\Repositories\DepartmentRepository;

class DepartmentController extends Controller
{
    private $departmentRepository;

    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/departments",
     *     summary="Get all departments",
     *     description="Fetch all departments with optional search and pagination.",
     *     tags={"Departments"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search term for department name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Filter departments by exact name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of departments per page",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of departments",
     *         @OA\JsonContent(
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching departments: SQL error")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->departmentRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/api/departments",
     *     summary="Create a new department",
     *     description="Create a new department with the specified data.",
     *     tags={"Departments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Marketing"),
     *             @OA\Property(property="description", type="string", example="Handles marketing and promotion")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Department created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department created successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating department: SQL error")
     *         )
     *     )
     * )
     */
    public function store(DepartmentRequest $request)
    {
        return $this->departmentRepository->store($request);
    }


    /**
     * @OA\Get(
     *     path="/api/departments/{id}",
     *     summary="Get a department by ID",
     *     description="Fetch a department by its ID.",
     *     tags={"Departments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error fetching department: SQL error")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        return $this->departmentRepository->show($id);
    }

    /**
     * @OA\Put(
     *     path="/api/departments/{id}",
     *     summary="Update a department",
     *     description="Update a department's details by its ID.",
     *     tags={"Departments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Marketing"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department updated successfully!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating department: SQL error")
     *         )
     *     )
     * )
     */
    public function update(DepartmentRequest $request, $id)
    {
        return $this->departmentRepository->update($request, $id);
    }

    /**
     * @OA\Delete(
     *     path="/api/departments/{id}",
     *     summary="Delete a department",
     *     description="Delete a department by its ID.",
     *     tags={"Departments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the department to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Department deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Department not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Department not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting department: SQL error")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        return $this->departmentRepository->delete($id);
    }
}
