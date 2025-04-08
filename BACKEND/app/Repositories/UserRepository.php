<?php

namespace App\Repositories;

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Database\QueryException;
use App\Models\Users;
use App\Models\Tenant;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    private $apiResponse;

    private $licenseController;

    public function __construct(CustomApiResponse $customApiResponse, LicenseController $licenseController)
    {
        $this->apiResponse = $customApiResponse;
        $this->licenseController = $licenseController;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $name = $request->input('name', '');
            $email = $request->input('email', '');
            $status = $request->input('status', '');
            $mobile = $request->input('mobile', '');
            $usergroup_id = $request->input('usergroup_id', '');

            $token = $request->bearerToken();
            $payload = JWTAuth::setToken($token)->getPayload();
            // Start the query for tenants

            $is_all_users = $request->has('all_user') ? $request->input('all_user') : null;

            $query = Users::query();

            if ($is_all_users !== 'Y') {
                $query->where('type', '3');

                // If there's a search term, apply it to the query
                if (!empty($searchTerm)) {
                    $query->where(function ($q) use ($searchTerm) {
                        $q->where('username', 'like', '%' . $searchTerm . '%')
                            ->orwhere('first_name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('email', 'like', '%' . $searchTerm . '%')
                            ->orWhere('mobile', 'like', '%' . $searchTerm . '%'); // Add any additional fields as needed
                    });
                }

                if (!empty($name)) {
                    $query->where('first_name', $name)->orWhere('last_name', $name);
                }
                if (!empty($email)) {
                    $query->where('email', $email);
                }
                if (!empty($status)) {
                    $query->where('status', $status);
                }
                if (!empty($mobile)) {
                    $query->where('mobile', $mobile);
                }
                if (!empty($usergroup_id)) {
                    $query->where('usergroup_id', $usergroup_id);
                }

                // Paginate the results
                $perPage = $request->input('per_page', config('omni.PER_PAGE'));
                $users = $query->paginate($perPage);
                //  $newToken = JWTAuth::refresh(JWTAuth::getToken());
                // Return the response
                return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.fetch_data'));
            }

            $users = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.fetch_data'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error during User List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $is_tanant = true;
            $licenseResponse = $this->licenseController->decodeLicense(new Request(['is_tanant' => $is_tanant]));
            $licenseInfo = $licenseResponse->getData(true);

            $maxAgents = $licenseInfo['number_of_agents'];

            $currentAgentCount = Users::where('type', '3')
                ->count();

            // Check if the limit is reached
            if ($currentAgentCount >= $maxAgents) {
                return response()->json([
                    'message' => trans("message.user_limit_reached"),
                ], ResponseHTTP::HTTP_BAD_REQUEST);
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
                "role_id" => $request->role_id,
                "usergroup_id" => $request->usergroup_id,
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

            return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.users_created'), ResponseHTTP::HTTP_CREATED);
        } catch (QueryException $e) {
            generateLogs('error', 'Error during User Store : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function update(Request $request, $user)
    {
        try {
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
                "role_id" => $request->role_id,
                "usergroup_id" => $request->usergroup_id,
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
            return $this->apiResponse->getResponseStructure(trans('message.success'), $user, trans('message.users_updated'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error during Update User : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($user)
    {
        try {
            $user->delete();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $user, trans('message.users_deleted'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error during Delete User : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($user)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $user, trans('message.fetch_data'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error during User Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = JWTAuth::setToken($token)->getPayload();
                $users = Users::findOrFail($payload['id']);
                if (!Hash::check($request->old_password, $users->password)) {
                    return response()->json(['error' => trans("message.old_password_incorrect")], ResponseHTTP::HTTP_FORBIDDEN);
                }
                if ($request->new_password != $request->new_password_confirmation) {
                    return response()->json(['error' => trans("message.password_not_match")], ResponseHTTP::HTTP_FORBIDDEN);
                }
                $users->password = Hash::make($request->new_password);
                $users->save();
                return response()->json(['message' => trans("message.password_updated")]);
            } else {
                return response()->json(['error' => trans("message.user_not_found")], ResponseHTTP::HTTP_FORBIDDEN);
            }
        } catch (QueryException $e) {
            generateLogs('error', 'Error during Update Password : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function myProfile($request)
    {
        try {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = JWTAuth::setToken($token)->getPayload();
                $users = Users::where('id', $payload['id'])->first();
                $return_array['profile_info'] = $users->toArray();
                unset($return_array['profile_info']['password']);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $return_array, trans('message.fetch_data'));
            } else {
                return response()->json(['error' => trans("message.user_not_found")], ResponseHTTP::HTTP_FORBIDDEN);
            }
        } catch (QueryException $e) {
            generateLogs('error', 'Error during My Profile : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function getBranding($request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => trans("message.invalid_credentials")], ResponseHTTP::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $domain = $payload['domain'];
            establishMasterConnection($domain, "omnichannel", "mysql");
            $tenant = Tenant::select('title', 'footer_content', 'footer_url', 'tenant_logo')
                ->where('domain_name', $domain)
                ->first();
            if (!$tenant) {
                return response()->json(['error' => trans("message.tenant_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.data_fetch'));
        } catch (\Exception $e) {
            generateLogs('error', 'Error during Get Branding: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
