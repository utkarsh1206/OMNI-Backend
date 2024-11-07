<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EcdialUser;
use Illuminate\Http\Request;

class UsersListController extends Controller
{
    public function index(Request $request)
    {
        $acl_user_group_qstring = ''; 
        
        $query = EcdialUser::query();

        if ($acl_user_group_qstring) {
            $query->whereRaw($acl_user_group_qstring);
        }

        $users = $query->where('user_level', '<', 8)
                       ->orderBy('user')
                       ->get(['user']);

        // Return the response
        return response()->json($users);
    }
}
