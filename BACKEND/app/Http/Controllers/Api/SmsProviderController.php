<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsProvider;


class SmsProviderController extends Controller
{
    public function index()
    {
            $providers = SmsProvider::where("status","active")->get();
                return response()->json([
                    'data' => $providers,
                ]);    
    }
}
