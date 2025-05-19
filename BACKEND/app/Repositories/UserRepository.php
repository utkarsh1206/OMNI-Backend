<?php

namespace App\Repositories;

use App\Http\Controllers\Api\LicenseController;
use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Admins;
use App\Models\Role;
use App\Models\Users;
use App\Models\Tenant;
use App\Models\UserGroup;
use Exception;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class UserRepository
{
    private $apiResponse;

    private $licenseController;

    public function __construct(CustomApiResponse $customApiResponse, LicenseController $licenseController)
    {
        $this->apiResponse = $customApiResponse;
        $this->licenseController = $licenseController;
    }

    // public function list(Request $request)
    // {
    //     try {
    //         $searchTerm = $request->input('search', '');
    //         $name = $request->input('name', '');
    //         $email = $request->input('email', '');
    //         $status = $request->input('status', '');
    //         $mobile = $request->input('mobile', '');
    //         $usergroup_id = $request->input('usergroup_id', '');
    //         $level = $request->input('level');

    //         $payload = extractPayload($request->bearerToken());
    //         // Start the query for tenants

    //         $is_all_users = $request->has('all_user') ? $request->input('all_user') : null;

    //         $query = Users::query();

    //         if ($is_all_users !== 'Y') {
    //             $query->where('type', '3');

    //             // If there's a search term, apply it to the query
    //             if (!empty($searchTerm)) {
    //                 $query->where(function ($q) use ($searchTerm) {
    //                     $q->where('username', 'like', '%' . $searchTerm . '%')
    //                         ->orwhere('first_name', 'like', '%' . $searchTerm . '%')
    //                         ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
    //                         ->orWhere('email', 'like', '%' . $searchTerm . '%')
    //                         ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
    //                         ->orWhere('level', 'like', '%' . $searchTerm . '%');
    //                 });
    //             }

    //             if (!empty($name)) {
    //                 $query->where('first_name', $name)->orWhere('last_name', $name);
    //             }
    //             if (!empty($email)) {
    //                 $query->where('email', $email);
    //             }
    //             if (!empty($status)) {
    //                 $query->where('status', $status);
    //             }
    //             if (!empty($mobile)) {
    //                 $query->where('mobile', $mobile);
    //             }
    //             if (!empty($usergroup_id)) {
    //                 $query->where('usergroup_id', $usergroup_id);
    //             }
    //             if (!empty($level)) {
    //                 if (is_array($level)) {
    //                     $query->whereIn('level', $level);
    //                 } else {
    //                     $query->where('level', $level);
    //                 }
    //             }

    //             // Paginate the results
    //             $perPage = $request->input('per_page', config('omni.PER_PAGE'));
    //             $users = $query->paginate($perPage);
    //             //  $newToken = JWTAuth::refresh(JWTAuth::getToken());
    //             // Return the response
    //             return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.fetch_data'));
    //         }

    //         $users = $query->get();
    //         return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.fetch_data'));
    //     } catch (Exception $e) {
    //         generateLogs('error', 'Error during User List : ', $e->getMessage());
    //         return $this->apiResponse->handleAndResponseException($e);
    //     }
    // }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');

            // Advanced filters
            $filters = [
                'name' => $request->input('name'), // matched against first_name and last_name
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'status' => $request->input('status'),
                'mobile' => $request->input('mobile'),
                'usergroup_id' => $request->input('usergroup_id'),
                'gender' => $request->input('gender'),
                // 'user_role_id' => $request->input('user_role_id'),
                'level' => $request->input('level'),
            ];

            $is_all_users = $request->input('all_user');
            $query = Users::query();

            if ($is_all_users !== 'Y') {
                $query->where('type', '3');
            }

            // ✅ LIVE SEARCH — searches across multiple text fields
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('username', 'like', "%{$searchTerm}%")
                        ->orWhere('first_name', 'like', "%{$searchTerm}%")
                        ->orWhere('last_name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%")
                        ->orWhere('mobile', 'like', "%{$searchTerm}%");
                    // ->orWhere('level', 'like', "%{$searchTerm}%");

                    // You can expand this block to include usergroup name, role name, etc., via joins if needed
                });
            }

            // ✅ ADVANCED FILTERS — applies only if specific filter field is given
            if (!empty($filters['name'])) {
                $name = $filters['name'];
                $query->where(function ($q) use ($name) {
                    $q->where('first_name', 'like', "%{$name}%")
                        ->orWhere('last_name', 'like', "%{$name}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$name}%"]);
                });
            }

            if (!empty($filters['username'])) {
                $query->where('username', 'like', "%{$filters['username']}%");
            }

            if (!empty($filters['email'])) {
                $query->where('email', 'like', "%{$filters['email']}%");
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['mobile'])) {
                $query->where('mobile', 'like', "%{$filters['mobile']}%");
            }

            if (!empty($filters['usergroup_id'])) {
                $query->where('usergroup_id', $filters['usergroup_id']);
            }

            if (!empty($filters['gender'])) {
                $query->where('gender', $filters['gender']);
            }

            // if (!empty($filters['user_role_id'])) {
            //     $query->where('user_role_id', $filters['user_role_id']);
            // }

            if (!empty($filters['level'])) {
                if (is_array($filters['level'])) {
                    $query->whereIn('level', $filters['level']);
                } else {
                    $query->where('level', $filters['level']);
                }
            }

            // ✅ PAGINATION
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $users = $page == config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $users,
                trans('message.fetch_data')
            );
        } catch (Exception $e) {
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
                    'error' => trans("message.user_limit_reached"),
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
                "domain" => auth()->user()->domain,
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

            if ($request->filled('user_role_id')) {
                $roles = Role::find([$request->user_role_id]);
                $users->roles()->sync($roles);
            }

            $config = getNeoxConfig();
            $ip = $config['neox_local_ip'];

            $userGroup = UserGroup::find($request->usergroup_id);
            if ($userGroup) {
                $neoxBody = [
                    'userId' => $request->username,
                    'password' => $request->password,
                    'fullName' => $request->first_name . ' ' . $request->last_name,
                    'userGroup' => $userGroup->name,
                ];
                $url = "https://{$ip}/Neox_ContactCenter_API/crud/user/create.php";
                sendHttpRequest($url, 'POST', $neoxBody);
            }

            return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.users_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $e) {
            generateLogs('error', 'Error sending Neox API request : ', $e->getMessage());
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
            if ($request->filled('user_role_id')) {
                $roles = Role::find([$request->user_role_id]);
                $user->roles()->sync($roles);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $user, trans('message.users_updated'));
        } catch (Exception $e) {
            generateLogs('error', 'Error during Update User : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }


    public function delete($user)
    {
        try {
            $username = $user->username; // Capture before delete
            $user->delete();

            // Fetch config and build webhook URL
            $config = getNeoxConfig();
            $ip = $config['neox_local_ip'];
            $url = "https://{$ip}/Neox_ContactCenter_API/crud/user/delete.php";

            // Prepare payload
            $data = ['userId' => $username];

            // Send HTTP request to webhook
            sendHttpRequest($url, 'DELETE', $data);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $user,
                trans('message.users_deleted')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in sending User Delete Neox API : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function show($user)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $user, trans('message.fetch_data'));
        } catch (Exception $e) {
            generateLogs('error', 'Error during User Show : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = extractPayload($token);
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
        } catch (Exception $e) {
            generateLogs('error', 'Error during Update Password : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function updateAdminPassword(Request $request)
    {
        try {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = extractPayload($token);
                if (!$payload['role_id'] == 0) {
                    return response()->json(['error' => "message.unauthorized_access"], ResponseHTTP::HTTP_FORBIDDEN);
                }

                $admin = Admins::findOrFail($payload['id']);
                if (!Hash::check($request->old_password, $admin->password)) {
                    return response()->json(['error' => trans("message.old_password_incorrect")], ResponseHTTP::HTTP_FORBIDDEN);
                }
                if ($request->new_password != $request->new_password_confirmation) {
                    return response()->json(['error' => trans("message.password_not_match")], ResponseHTTP::HTTP_FORBIDDEN);
                }
                $admin->password = Hash::make($request->new_password);
                $admin->save();
                return response()->json(['message' => trans("message.password_updated")]);
            } else {
                return response()->json(['error' => trans("message.user_not_found")], ResponseHTTP::HTTP_FORBIDDEN);
            }
        } catch (Exception $e) {
            generateLogs('error', 'Error during Update Password : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function myProfile($request)
    {
        try {
            $token = $request->bearerToken();
            if ($token != "") {
                $payload = extractPayload($token);
                $users = Users::where('id', $payload['id'])->first();
                $return_array['profile_info'] = $users->toArray();
                unset($return_array['profile_info']['password']);
                return $this->apiResponse->getResponseStructure(trans('message.success'), $return_array, trans('message.fetch_data'));
            } else {
                return response()->json(['error' => trans("message.user_not_found")], ResponseHTTP::HTTP_FORBIDDEN);
            }
        } catch (Exception $e) {
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
            $payload = extractPayload($token);
            $domain = $payload['domain'];
            establishMasterConnection($domain, "omnichannel", "mysql");
            $tenant = Tenant::select('title', 'footer_content', 'footer_url', 'tenant_logo')
                ->where('domain_name', $domain)
                ->first();
            if (!$tenant) {
                return response()->json(['error' => trans("message.tenant_not_found")], ResponseHTTP::HTTP_NOT_FOUND);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $tenant, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error during Get Branding: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
