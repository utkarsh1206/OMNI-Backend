<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(title="Omni Api", version="0.1")
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
