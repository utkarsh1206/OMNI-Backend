<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        // "tenant_id",
        "type",
        "first_name",
        "last_name",
        "email",
        "username",
        "password",
        "domain",
        "mobile",
        "status",
        "role_id",
        "usergroup_id",
        "name_prefix",
        "level",
        "date_of_birth",
        "country_id",
        "state_id",
        "city_id",
        "occupation_id",
        "pincode",
        "address",
        "gender"
    ];

    protected $addPhoneToJWT = false;

    // Method to toggle the addition of phone
    public function setAddPhoneToJWT($flag)
    {
        if ($flag == 2) {
            $this->addPhoneToJWT = true;
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Typically the primary key (id)
    }

    // public function getJWTCustomClaims()
    // {
    //     $permission = "";
    //     $permission_array = [];

    //     $postData = Request::all(); // Accessing all post data

    //     $tenantRoleId = DB::table('omnichannel.tenants')
    //         ->where('domain_name', $postData['domain'])
    //         ->value('tenant_role_id');

    //     if ($tenantRoleId) {
    //         $permission = DB::table('omnichannel.tenant_roles')
    //             ->where('id', $tenantRoleId)
    //             ->value('permission');
    //     }

    //     if (!empty(json_decode($permission, true))) {
    //         foreach (json_decode($permission, true) as $permission_key => $permission_value) {
    //             if ($permission_value['view'] == 1) {
    //                 if (!empty(json_decode($permission, true))) {
    //                     foreach (json_decode($permission, true) as $permission_key => $permission_value) {
    //                         if ($permission_value['view'] == 1) {
    //                             $view_name = DB::table('omnichannel.menu_permissions')
    //                                 ->where('module_name', $permission_key)
    //                                 ->value('view_name');
    //                             $permission_array[] = $view_name;
    //                         }
    //                     }
    //                 }

    //                 $campaign = isset($postData['campaign']) ? $postData['campaign'] : null;
    //                 $phone = isset($postData['phone']) ? $postData['phone'] : null;

    //                 $claims = [
    //                     'id' => $this->id,
    //                     'username' => $this->username,
    //                     'email' => $this->email,
    //                     'role_id' => $this->role_id,
    //                     'domain' =>  $postData['domain'],
    //                     'permission' => json_encode($permission_array),
    //                 ];

    //                 if ($postData['flag'] == 2) {
    //                     $claims['campaign'] = $campaign;
    //                     $claims['phone'] = $phone;
    //                 }

    //                 // Conditionally add phone to JWT claims if flag == 2
    //                 // if ($this->addPhoneToJWT) {
    //                 //     $claims['phone'] = $this->phone;
    //                 // }

    //                 return $claims;
    //             }
    //         }
    //     }
    // }

    public function getJWTCustomClaims()
    {
        $permission = "";
        $permission_array = [];

        $postData = Request::all(); // Accessing all post data

        $tenantRoleId = DB::table('omnichannel.tenants')
            ->where('domain_name', $postData['domain'])
            ->value('tenant_role_id');

        if ($tenantRoleId) {
            $permission = DB::table('omnichannel.tenant_roles')
                ->where('id', $tenantRoleId)
                ->value('permission');
        }

        $decoded_permissions = json_decode($permission, true);
        if (!empty($decoded_permissions)) {
            foreach ($decoded_permissions as $permission_key => $permission_value) {
                if (isset($permission_value['allowed']) && $permission_value['allowed'] == 1) {
                    $view_name = DB::table('omnichannel.menu_permissions')
                        ->where('module_name', $permission_key)
                        ->value('view_name');
                    if ($view_name) {
                        $permission_array[] = $view_name;
                    }
                }
            }

            $campaign = isset($postData['campaign']) ? $postData['campaign'] : null;
            $phone = isset($postData['phone']) ? $postData['phone'] : null;

            $claims = [
                'id' => $this->id,
                'username' => $this->username,
                'email' => $this->email,
                'role_id' => $this->role_id,
                'domain' => $postData['domain'],
                //    'permission' => json_encode($permission_array),
            ];

            if ($postData['flag'] == 2) {
                $claims['campaign'] = $campaign;
                $claims['phone'] = $phone;
            }

            return $claims;
        }
    }

    public function liveAgent()
    {
        return $this->hasOne(LiveAgents::class, 'user_id');
    }

    protected function status(): Attribute
    {
        return Attribute::get(function () {
            return $this->liveAgent ? 'available' : 'offline';
        });
    }
}
