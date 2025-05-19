<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $appends = ['user_role_id', 'online_status', 'usergroup_name'];

    protected $hidden = ['usergroup'];

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

    public function getJWTCustomClaims()
    {
        $postData = Request::all();

        $claims = [
            'id'       => $this->id,
            'username' => $this->username,
            'email'    => $this->email,
            'role_id'  => $this->role_id,
            'domain'   => $postData['domain'] ?? null,
        ];

        if (isset($postData['flag']) && $postData['flag'] == 2) {
            // New permission format using IDs
            $permissions = [];

            foreach ($this->roles as $role) {
                foreach ($role->tenantModules as $module) {
                    foreach ($module->actions as $action) {
                        $permissions[$module->name][] = $action->name;
                    }
                }
            }

            // Remove duplicates in action ID arrays
            foreach ($permissions as &$actions) {
                $actions = array_values(array_unique($actions));
            }

            $claims['permission'] = json_encode($permissions);

            // Optional campaign & phone
            if (!empty($postData['campaign'])) {
                $claims['campaign'] = $postData['campaign'];
            }
            if (!empty($postData['phone'])) {
                $claims['phone'] = $postData['phone'];
            }
        } else {
            // Old logic: build permission array from omnichannel.tenant_roles
            $permission = "";
            $permission_array = [];

            $tenantRoleId = DB::table('omnichannel.tenants')
                ->where('domain_name', $postData['domain'] ?? null)
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
            }

            $claims['permission'] = json_encode($permission_array);

            $dbName = DB::table('omnichannel.details')->select('db')->where('domain', $postData['domain'])->first();
            $claims['db'] = $dbName->db;
        }

        return $claims;
    }


    public function liveAgent()
    {
        return $this->hasOne(LiveAgents::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    // Virtual attribute to determine online status
    public function getOnlineStatusAttribute(): string
    {
        return $this->liveAgent ? 'online' : 'offline';
    }

    /**
     * Check if user has a given module-action
     */
    public function hasPermission(string $moduleName, string $actionName): bool
    {
        $mod = TenantModule::where('name', $moduleName)->first();

        if (! $mod) {
            return false;
        }

        return $this->roles()
            ->whereHas('actions', function ($q) use ($actionName, $mod) {
                $q->where('name', $actionName)
                    ->wherePivot('tenant_module_id', $mod->id);
            })->exists();
    }

    public function getUserRoleIdAttribute()
    {
        return $this->roles()->first()?->id;
    }

    public function usergroup()
    {
        return $this->belongsTo(UserGroup::class, 'usergroup_id');
    }

    public function getUsergroupNameAttribute()
    {
        return $this->usergroup?->name;
    }

    public function getLatestMessageAttribute()
    {
        return Message::where('type', 'agent_chat')
            ->betweenUsers(auth()->id(), $this->id)
            ->orderByDesc('created_at')
            ->first();
    }

    public function getUnreadMessageAttribute()
    {
        return Message::where('is_read', 'unread')
            ->betweenUsers(auth()->id(), $this->id)
            ->count();
    }
}
