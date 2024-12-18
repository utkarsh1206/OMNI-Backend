<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Phones;
use App\Http\Resources\PhoneResource;

class PhoneController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm = $request->input('search', '');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = Phones::query();

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('extension', 'like', "%$searchTerm%")
                    ->orWhere('login', 'like', "%$searchTerm%")
                    ->orWhere('status', 'like', "%$searchTerm%");
            });
        }

        if ($page === 'all') {
            $phones = $query->get();
            return response()->json([
                'data' => $phones,
            ]);
        } else {
            $phones = $query->paginate($perPage);
            return response()->json([
                'meta' => [
                    'current_page' => $phones->currentPage(),
                    'last_page' => $phones->lastPage(),
                    'per_page' => $phones->perPage(),
                    'total' => $phones->total(),
                ],
                'data' => $phones->items(),
            ]);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'extension' => 'required|unique:phones,extension',
            'login' => 'required||unique:phones,login|alpha_num|between:2,320',
            'pass' => 'required|alpha_num|between:2,320',
            'status' => 'required',
            'local_gmt' => 'nullable|numeric',
        ]);

        $validatedData['local_gmt'] = $validatedData['local_gmt'] ?? '5.50';
        $validatedData['dialplan_number'] = $validatedData['extension'];
        $validatedData['voicemail_id'] = $validatedData['extension'];
        $validatedData['active'] = "Y";

        $phone = Phones::create($validatedData);
        return response()->json([
            'message' => 'Phone created successfully',
            'data' => new PhoneResource($phone)
        ], 201);
    }

    public function show($id)
    {
        $phone = Phones::findOrFail($id);
        return new PhoneResource($phone);
    }

    public function update(Request $request, $extension)
    {
        $phone = Phones::where('extension', $extension)->first();

        if (!$phone) {
            return response()->json(['message' => 'Phone not found'], 404);
        }

        $validatedData = $request->validate([
            'extension' => 'required|unique:phones,extension',
            'login' => 'required|unique:phones,login|alpha_num|between:2,320',
            'pass' => 'required|alpha_num|between:2,320',
            'status' => 'required',
            'local_gmt' => 'nullable|numeric',
        ]);

        $validatedData['local_gmt'] = $validatedData['local_gmt'] ?? '5.50';

        $phone->update($validatedData);
        return response()->json([
            'message' => 'Phone updated successfully',
            'data' => new PhoneResource($phone)
        ], 200);
    }

    public function destroy($extension)
    {
        $phone = Phones::where('extension', $extension)->first();

        if (!$phone) {
            return response()->json(['message' => 'Phone not found'], 404);
        }

        $phone->delete();
        return response()->json(['message' => 'Phone deleted successfully'], 200);
    }
}
