<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HashController extends Controller
{
    /**
     * Takes an input string and returns its bcrypt hash.
     *
     * @param string $input The string to be hashed.
     * @return \Illuminate\Http\JsonResponse
     */
    public function hashString(string $input)
    {
        // Use the Hash facade to generate the bcrypt hash
        $hashedValue = Hash::make($input);

        // Return the input and the hash as a JSON response
        return response()->json([
            'input_string' => $input,
            'bcrypt_hash' => $hashedValue,
            'message' => 'Input successfully hashed using bcrypt.',
        ]);
    }
}


// <?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;

// class HashController extends Controller
// {
//     /**
//      * Takes an input string and returns its bcrypt hash.
//      *
//      * @param string $input The string to be hashed.
//      * @return \Illuminate\Http\JsonResponse
//      */
//     public function hashString(string $input)
//     {
//         // Use the Hash facade to generate the bcrypt hash
//         $hashedValue = Hash::make($input);

//         // Return the input and the hash as a JSON response
//         return response()->json([
//             'input_string' => $input,
//             'bcrypt_hash' => $hashedValue,
//             'message' => 'Input successfully hashed using bcrypt.',
//         ]);
//     }
// }