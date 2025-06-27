<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLimitRequest;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserChangeStatusRequest;
use App\Http\Requests\DcUserLogoutRequest;
use App\Models\Detail;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     summary="List Users",
     *     description="Fetch a list of users with optional filters and pagination.",
     *     operationId="listUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter users by username, first name, last name, email, or mobile.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter users by first name or last name.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter users by email.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter users by status (active, inactive, etc.).",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="Filter users by mobile number.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="usergroup_id",
     *         in="query",
     *         description="Filter users by user group ID.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="all_user",
     *         in="query",
     *         description="Set to 'Y' to fetch all users, or leave empty to filter users by type 3.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users to return per page.",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched the list of users.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Data fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=100
     *                 ),
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="username", type="string"),
     *                         @OA\Property(property="first_name", type="string"),
     *                         @OA\Property(property="last_name", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="mobile", type="string"),
     *                         @OA\Property(property="status", type="string"),
     *                         @OA\Property(property="usergroup_id", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request due to missing or incorrect parameters.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Invalid parameters."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthorized."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="An error occurred."
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        return $this->userRepository->list($request);
    }

    /**
     * @OA\Post(
     *     path="/users/store",
     *     summary="Create a new user",
     *     description="Creates a new user. The user is subject to the limit of agents based on the license.",
     *     operationId="storeUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to create a new user.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "first_name", "last_name", "email", "password", "mobile", "status", "role_id", "usergroup_id", "name_prefix", "level", "date_of_birth", "country_id", "state_id", "city_id", "occupation_id", "pincode", "address", "gender"},
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="securepassword123"),
     *                 @OA\Property(property="mobile", type="string", example="1234567890"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=1),
     *                 @OA\Property(property="usergroup_id", type="integer", example=2),
     *                 @OA\Property(property="name_prefix", type="string", example="Mr."),
     *                 @OA\Property(property="level", type="integer", example=1),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="123456"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, Some City, Some Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="1234567890"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=1),
     *                 @OA\Property(property="usergroup_id", type="integer", example=2),
     *                 @OA\Property(property="name_prefix", type="string", example="Mr."),
     *                 @OA\Property(property="level", type="integer", example=1),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="123456"),
     *                 @OA\Property(property="address", type="string", example="123 Main St, Some City, Some Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="User limit reached or bad request.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User limit reached.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred.")
     *         )
     *     )
     * )
     */
    public function store(UserRequest $request)
    {
        return $this->userRepository->store($request);
    }

    /**
     * @OA\Get(
     *     path="/users/show/{user}",
     *     summary="Get details of a specific user",
     *     description="Fetches the details of a user by their ID.",
     *     operationId="showUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the user whose details need to be fetched.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9876543210"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="usergroup_id", type="integer", example=3),
     *                 @OA\Property(property="name_prefix", type="string", example="Dr."),
     *                 @OA\Property(property="level", type="integer", example=2),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="654321"),
     *                 @OA\Property(property="address", type="string", example="456 Elm St, Another City, Another Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request. Missing or invalid parameters.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while fetching the user data.")
     *         )
     *     )
     * )
     */
    public function show(Users $user)
    {
        return $this->userRepository->show($user);
    }


    /**
     * @OA\Put(
     *     path="/users/update/{user}",
     *     summary="Update an existing user",
     *     description="Updates the details of an existing user based on the provided user ID.",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the user to be updated.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to update the existing user.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "first_name", "last_name", "email", "password", "mobile", "status", "role_id", "usergroup_id", "name_prefix", "level", "date_of_birth", "country_id", "state_id", "city_id", "occupation_id", "pincode", "address", "gender"},
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="newsecurepassword123"),
     *                 @OA\Property(property="mobile", type="string", example="9876543210"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="usergroup_id", type="integer", example=3),
     *                 @OA\Property(property="name_prefix", type="string", example="Dr."),
     *                 @OA\Property(property="level", type="integer", example=2),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="654321"),
     *                 @OA\Property(property="address", type="string", example="456 Elm St, Another City, Another Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9876543210"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="usergroup_id", type="integer", example=3),
     *                 @OA\Property(property="name_prefix", type="string", example="Dr."),
     *                 @OA\Property(property="level", type="integer", example=2),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="654321"),
     *                 @OA\Property(property="address", type="string", example="456 Elm St, Another City, Another Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request. Missing or invalid parameters.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the user.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Users $user)
    {
        return $this->userRepository->update($request, $user);
    }

    /**
     * @OA\Put(
     *     path="/my-profile/{user}",
     *     summary="Update authenticated user's profile",
     *     description="Allows an authenticated user to update their profile details.",
     *     operationId="profileUpdate",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the authenticated user.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Profile data to update the authenticated user.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "first_name", "last_name", "email", "mobile", "status", "role_id", "usergroup_id", "name_prefix", "level", "date_of_birth", "country_id", "state_id", "city_id", "occupation_id", "pincode", "address", "gender"},
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9876543210"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="usergroup_id", type="integer", example=3),
     *                 @OA\Property(property="name_prefix", type="string", example="Dr."),
     *                 @OA\Property(property="level", type="integer", example=2),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="654321"),
     *                 @OA\Property(property="address", type="string", example="456 Elm St, Another City, Another Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="mobile", type="string", example="9876543210"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="usergroup_id", type="integer", example=3),
     *                 @OA\Property(property="name_prefix", type="string", example="Dr."),
     *                 @OA\Property(property="level", type="integer", example=2),
     *                 @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                 @OA\Property(property="country_id", type="integer", example=101),
     *                 @OA\Property(property="state_id", type="integer", example=202),
     *                 @OA\Property(property="city_id", type="integer", example=303),
     *                 @OA\Property(property="occupation_id", type="integer", example=404),
     *                 @OA\Property(property="pincode", type="string", example="654321"),
     *                 @OA\Property(property="address", type="string", example="456 Elm St, Another City, Another Country"),
     *                 @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="female")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error)."
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token)."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error."
     *     )
     * )
     */
    public function profileUpdate(Request $request, Users $user)
    {
        return $this->userRepository->profileUpdate($request, $user);
    }

    /**
     * @OA\Delete(
     *     path="/users/delete/{user}",
     *     summary="Delete an existing user",
     *     description="Deletes an existing user based on the provided user ID.",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         description="The ID of the user to be deleted.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User deleted successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="first_name", type="string", example="John"),
     *                 @OA\Property(property="last_name", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                 @OA\Property(property="status", type="string", example="deleted")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request. Missing or invalid parameters.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access (invalid token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="An error occurred while deleting the user.")
     *         )
     *     )
     * )
     */
    public function destroy(Users $user)
    {
        return $this->userRepository->delete($user);
    }

    /**
     * @OA\Put(
     *     path="/users/update-tenantpassword",
     *     summary="Update user password",
     *     description="Allows the user to update their password by providing their old password, new password, and password confirmation.",
     *     operationId="updatePassword",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", example="old_password123"),
     *             @OA\Property(property="new_password", type="string", example="new_password123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="new_password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized (invalid or missing token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized access.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (incorrect old password or passwords do not match).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Old password is incorrect.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found (invalid token or user does not exist).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="An error occurred while updating the password.")
     *         )
     *     )
     * )
     */
    public function updatepassword(Request $request)
    {
        return $this->userRepository->updatePassword($request);
    }

    /**
     * @OA\Put(
     *     path="/users/update-adminpassword",
     *     summary="Update admin password",
     *     description="Allows an administrator to update their password by providing their old password, new password, and password confirmation.",
     *     operationId="updateAdminPassword",
     *     tags={"Admins"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="old_password", type="string", example="old_admin_password"),
     *             @OA\Property(property="new_password", type="string", example="new_admin_password"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="new_admin_password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Admin password updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized (invalid or missing token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized access.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (invalid admin role, incorrect old password, or passwords do not match).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid admin role.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Admin not found (invalid token or admin does not exist).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Admin not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="An error occurred while updating the admin password.")
     *         )
     *     )
     * )
     */
    public function updateAdminPassword(Request $request)
    {
        return $this->userRepository->updateAdminPassword($request);
    }

    /**
     * @OA\Get(
     *     path="/users/my-profile",
     *     summary="Get current user's profile information",
     *     description="Fetches the profile information of the authenticated user using the provided bearer token.",
     *     operationId="myProfile",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched the user's profile information.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="profile_info", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="first_name", type="string", example="John"),
     *                     @OA\Property(property="last_name", type="string", example="Doe"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="mobile", type="string", example="9876543210"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="role_id", type="integer", example=2),
     *                     @OA\Property(property="usergroup_id", type="integer", example=3),
     *                     @OA\Property(property="name_prefix", type="string", example="Mr."),
     *                     @OA\Property(property="level", type="integer", example=1),
     *                     @OA\Property(property="date_of_birth", type="string", format="date", example="1985-06-15"),
     *                     @OA\Property(property="country_id", type="integer", example=101),
     *                     @OA\Property(property="state_id", type="integer", example=202),
     *                     @OA\Property(property="city_id", type="integer", example=303),
     *                     @OA\Property(property="occupation_id", type="integer", example=404),
     *                     @OA\Property(property="pincode", type="string", example="654321"),
     *                     @OA\Property(property="address", type="string", example="123 Main St, Some City, Some Country"),
     *                     @OA\Property(property="gender", type="string", enum={"male", "female", "other"}, example="male")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (validation error).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid input data.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized (invalid or missing token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthorized access.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (user not found).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="User not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="An error occurred while fetching the profile information.")
     *         )
     *     )
     * )
     */
    public function myprofile(Request $request)
    {
        return $this->userRepository->myprofile($request);
    }

    /**
     * @OA\Get(
     *     path="/branding",
     *     summary="Get tenant branding information",
     *     description="Fetches branding details like title, footer content, footer URL, and tenant logo based on the domain of the authenticated tenant.",
     *     operationId="getBranding",
     *     tags={"Branding"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved tenant branding information.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data fetched successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="title", type="string", example="Tenant Title"),
     *                 @OA\Property(property="footer_content", type="string", example="Footer Content Example"),
     *                 @OA\Property(property="footer_url", type="string", example="https://example.com/footer"),
     *                 @OA\Property(property="tenant_logo", type="string", example="https://example.com/logo.png")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized (invalid or missing token).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid credentials.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant not found (incorrect domain or tenant not available).",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Tenant not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="An error occurred while fetching the branding information.")
     *         )
     *     )
     * )
     */
    public function getBranding(Request $request)
    {
        return $this->userRepository->getBranding($request);
    }


    /**
     * @OA\Post(
     *     path="/api/check-user-limit",
     *     summary="Check if user creation exceeds license limit",
     *     description="Checks if the number of users (agent/manager) exceeds the limit defined in the tenant license.",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role_id"},
     *             @OA\Property(property="role_id", type="integer", example=3, description="ID of the role to check"),
     *             @OA\Property(property="type", type="integer", example=0, description="Role type (0 for manager, 1 for agent)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="License usage within limits",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Data fetched successfully"),
     *             @OA\Property(property="status", type="string", example="success")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User limit exceeded",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User limit exceeded")
     *         )
     *     )
     * )
     */
    public function checkUserLimit(UserLimitRequest $request)
    {
        return $this->userRepository->checkUserLimit($request);
    }

    /**
     * @OA\Post(
     *     path="/api/user/change-status",
     *     summary="Change user status",
     *     description="Change the status of a User (e.g., Active or Inactive) and update related user statuses.",
     *     operationId="changeTenantStatus",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "status"},
     *             @OA\Property(property="tenant_id", type="integer", example=1, description="ID of the tenant to update"),
     *             @OA\Property(property="status", type="string", example="Inactive", description="New status for the tenant")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="integer", example=1, description="Number of affected rows"),
     *             @OA\Property(property="details", type="string", example="User status updated successfully.")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred")
     *         )
     *     )
     * )
     */
    public function changeStatus(UserChangeStatusRequest $request)
    {
        return $this->userRepository->changeStatus($request);
    }

    public function dcUserLogout(DcUserLogoutRequest $request, Detail $detail)
    {
        $this->userRepository->dcUserLogout($request, $detail);
    }
}
