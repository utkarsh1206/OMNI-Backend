<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return Test::all();
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {
        $domain = $request->input('domain');

        if ($domain) 
        {
            $validatedData = $request->validate([
                'testDomain' => 'required|string|max:255',
                'test_ip' => 'required|string|min:7|max:15',
            ]);

            return Test::create([
                'testDomain' => $validatedData['testDomain'],
                'test_ip' => $validatedData['test_ip'],
            ]);
        } else {
            return response()->json(['error' => 'Domain is required'], 400);
        }
    }

    public function show(Test $test)
    {
        return $test;
    }

    public function update(Request $request, Test $test)
    {
        //
    }

    public function destroy(Test $test)
    {
        return $test->delete();
    }
}