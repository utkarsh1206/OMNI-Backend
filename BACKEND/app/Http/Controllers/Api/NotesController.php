<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notes;
use Illuminate\Http\Request;



class NotesController extends Controller
{
    public function index(Request $request)
    {
        // Validate the incoming request to ensure 'supportticket_id' is provided
        $request->validate([
            'supportticket_id' => 'required|integer', // Assuming you have a support_tickets table
        ]);
        $supportticketId = $request->input('supportticket_id');
        $notedetail = Notes::where('supportticket_id', $supportticketId)->orderBy('id', 'desc')->get();
        return response()->json($notedetail);
    }
    public function store(Request $request)
    {
        $notedetail = Notes::create([
            'supportticket_id'=>$request->supportticket_id,
            'date' =>gmdate('Y-m-d H:i:s'),
            'note' =>$request->note,
            'created_by' =>isset($request->user_id)?$request->user_id:0,
        ]);
        return response()->json([
            'message' => 'Note create successfully.',
            'data' => $notedetail
        ], 201);
    }
}
