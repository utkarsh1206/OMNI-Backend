<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Http\Resources\TenantsResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;



class UsersController extends Controller
{
    public function index(Request $request)
    {
        // Get the search term from the request
        $searchTerm = $request->input('search', '');
        $token = $request->bearerToken();
        $payload = JWTAuth::setToken($token)->getPayload();
        // Start the query for tenants
        
        $is_all_users = $request->has('all_user') ? $request->input('all_user') : null;

        $query = Users::query()->where('is_delete', 'No');

        if ($is_all_users !== 'Y') {
            $query->where('type', '3');

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('username', 'like', '%' . $searchTerm . '%')
                    ->orwhere('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
                    //->orWhere('domain_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('company_code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('company_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('server_serialnumber', 'like', '%' . $searchTerm . '%')
                    ->orWhere('server_ip', 'like', '%' . $searchTerm . '%')
                    ->orWhere('server_mac', 'like', '%' . $searchTerm . '%')
                    ->orWhere('domain_url', 'like', '%' . $searchTerm . '%')
                    ->orWhere('status', 'like', '%' . $searchTerm . '%'); // Add any additional fields as needed
            });
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);
      //  $newToken = JWTAuth::refresh(JWTAuth::getToken());
        // Return the response
        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
          //      'refresh_token'=>$newToken
            ],
]);
        }

        $users = $query->get();

        // Return all users
        return response()->json([
            'data' => $users,
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'email' => 'required|unique:users',
            // other validation rules...
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $users = Users::create([
            'username' => $request->username,
           // "tenant_id" => $request->tenant_id,
            "type" => 3,
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "email" => $request->email,
            "username" => $request->username,
            "password" => bcrypt($request->password),
           // "domain" => $request->domain,
            "mobile" => $request->mobile,
            "status" => $request->status,
            "is_delete" => 'No',
            "role_id" => $request->role_id,
            "name_prefix" => $request->name_prefix,
            "level" => $request->level,
            "date_of_birth" => $request->date_of_birth,
            "country_id" => $request->country_id,
            "state_id" => $request->state_id,
            "city_id" => $request->city_id,
            "occupation_id" => $request->occupation_id,
            "pincode" => $request->pincode,
            "address" => $request->address,
            "gender" => $request->gender      
        ]);
        return response()->json([
            'message' => 'User Created Successfully',
            'data' => new TenantsResource($users)
        ]);
    }
    public function show(Users $user)
    {
        return new TenantsResource($user);
    }
    public function update(Request $request, Users $user)
    {
        $user->update([
            'username' => $request->username,
           // "tenant_id" => $request->tenant_id,
            "type" => 3,
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "email" => $request->email,
            "username" => $request->username,
            "password" => bcrypt($request->password),
            //"domain" => $request->domain,
            "mobile" => $request->mobile,
            "status" => $request->status,
            "is_delete" => $request->is_delete,
            "role_id" => $request->role_id,
            "name_prefix" => $request->name_prefix,
            "level" => $request->level,
            "date_of_birth" => $request->date_of_birth,
            "country_id" => $request->country_id,
            "state_id" => $request->state_id,
            "city_id" => $request->city_id,
            "occupation_id" => $request->occupation_id,
            "pincode" => $request->pincode,
            "address" => $request->address,
            "gender" => $request->gender       
        ]);
        return response()->json([
            'message' => 'User Updated Successfully',
            'data' => new TenantsResource($user)
        ]);
    }    
    public function destroy(Users $user)
    {
        $user->is_delete = 'Yes';
        $user->save();
        return response()->json([
            'message' => 'User Deleted Successfully',
        ]);
    }
    public function updatepassword(Request $request){
    $token = $request->bearerToken();
        if($token != ""){
            $payload = JWTAuth::setToken($token)->getPayload();
            $users = Users::findOrFail($payload['id']);
            if (!Hash::check($request->old_password, $users->password)) {
                return response()->json(['error' => 'Old Password Is Incorrect.'], 403);
            }
            if($request->new_password != $request->new_password_confirmation){
                return response()->json(['error' => 'New Password And Confirm Not Matched.'], 403);
            }
            $users->password = Hash::make($request->new_password);
            $users->save();
            return response()->json(['message' => 'Password Updated Successfully.']);
        }else{
            return response()->json(['error' => 'User Not Found.'], 403);
        }
    }
    public function myprofile(Request $request){
        $token = $request->bearerToken();
        if($token != ""){
            $payload = JWTAuth::setToken($token)->getPayload();
            $domain =$payload['domain'];
            $tenants = DB::table('omnichannel.tenants')
            ->select('title', 'footer_content', 'footer_url', 'tenant_logo')
            ->where('domain_name', '=', $domain) 
            ->get();
            $return_array = array();
            $return_array['branding_information'] = (array)$tenants[0];
            $users = Users::where('id', $payload['id'])->first();
            $return_array['profile_info'] = $users->toArray();
            unset($return_array['profile_info']['password']);
            return response()->json([
                'data' => new TenantsResource($return_array)
            ]);
        }else{
            return response()->json(['error' => 'User Not Found.'], 403);
        }
    }
}
