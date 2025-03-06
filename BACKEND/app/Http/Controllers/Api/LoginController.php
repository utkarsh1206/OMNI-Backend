<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Repositories\LoginRepository;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    private $loginRepository;

    public function __construct(LoginRepository $loginRepository)
    {
        $this->loginRepository = $loginRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="User Login",
     *     description="User logs in with either username or email and password. Depending on the flag, it can also verify domain and phone credentials.",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"username", "password", "flag"},
     *             @OA\Property(property="username", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="flag", type="integer", example=0, description="Flag to indicate the type of login. 0 for admin, 1 for user, 2 for phone login"),
     *             @OA\Property(property="domain", type="string", example="example.com", description="Optional domain input, required for flag 1 or 2"),
     *             @OA\Property(property="phone", type="string", example="1234567890", description="Optional phone input, required for flag 2"),
     *             @OA\Property(property="phone_password", type="string", format="password", example="phonepassword", description="Optional phone password, required for flag 2")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="jwt.token.here"),
     *             @OA\Property(property="message", type="string", example="User Login successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (e.g. missing domain or invalid input)",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Domain is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized: Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden: License expired",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="License expired")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Database error")
     *         )
     *     )
     * )
     */
    public function login_action(LoginRequest $request)
    {
        try {
            $validator = $request->validated();
            return $this->loginRepository->login($request, $validator);
        } catch (Exception $e) {
            return response()->json(['error' => trans('message.invalid_credentials')], ResponseHTTP::HTTP_UNAUTHORIZED);
        }
    }

    /**
 * @OA\Post(
 *     path="/logout",
 *     summary="Logout user",
 *     description="Logs the user out by invalidating the JWT token.",
 *     operationId="logout",
 *     tags={"Authentication"},
 *     security={{
 *         "bearerAuth": {}
 *     }},
 *     @OA\Response(
 *         response=200,
 *         description="Successfully logged out",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Successfully logged out"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Token invalid or expired",
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *     )
 * )
 */
    public function logout(Request $request)
    {
        $this->loginRepository->logout($request);
        return response()->json(['message' => 'Successfully logged out']);
    }
}
