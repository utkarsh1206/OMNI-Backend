<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Repositories\OtpRepository;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    private $otpRepository;

    public function __construct(OtpRepository $otpRepository)
    {
        $this->otpRepository = $otpRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/customer-portal/request-otp/{uin}",
     *     summary="Request an OTP for a user",
     *     tags={"OTP"},
     *     operationId="requestOtp",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="uin",
     *         in="path",
     *         required=true,
     *         description="Unique Identification Number (UIN) of the user",
     *         @OA\Schema(
     *             type="string",
     *             example="12345"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "email"},
     *             @OA\Property(property="mobile", type="string", example="+91234567890", description="Mobile number of the user"),
     *             @OA\Property(property="email", type="string", example="user@example.com", description="Email of the user"),
     *             @OA\Property(property="message", type="string", example="Please enter the OTP sent to your mobile or email")
     *         ),
     *         @OA\XmlContent(
     *             @OA\Property(property="mobile", type="string", example="1234567890"),
     *             @OA\Property(property="email", type="string", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object", description="Empty data for success response"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or logical error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Mobile or Email is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Mobile or Email not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Mobile or Email does not exist in our records.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function requestOtp(RequestOtpRequest $request)
    {
        return  $this->otpRepository->requestOtp($request);
    }

    /**
     * @OA\Post(
     *     path="/api/customer-portal/verify-otp/{uin}",
     *     summary="Verify the OTP for a user",
     *     tags={"OTP"},
     *     operationId="verifyOtp",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="uin",
     *         in="path",
     *         required=true,
     *         description="Unique Identification Number (UIN) of the user",
     *         @OA\Schema(
     *             type="string",
     *             example="12345"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="123456", description="One-time password (OTP) entered by the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="object", description="User data and access token for the verified user"),
     *             @OA\Property(property="userinfo", type="object", description="User information such as mobile, email, username"),
     *             @OA\Property(property="token", type="string", example="Bearer <JWT_Token>")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation or logical error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid OTP.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OTP expired or not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP has expired or not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many OTP verification attempts",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Maximum OTP attempts exceeded.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */

    public function verifyOtp(VerifyOtpRequest $request)
    {
        return  $this->otpRepository->verifyOtpNew($request);
    }
}
