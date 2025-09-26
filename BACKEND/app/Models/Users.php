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

    protected $appends = ['user_role_id', 'online_status', 'usergroup_name', 'user_role_name'];

    protected $hidden = ['usergroup', 'password'];

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

    protected $uin, $userId;

    // Method to toggle the addition of phone
    public function setAddPhoneToJWT($flag)
    {
        if ($flag == 2) {
            $this->addPhoneToJWT = true;
        }
    }

    public function setUIN($uin)
    {
        $this->uin = $uin;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
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

        if (isset($postData['flag']) && (in_array($postData['flag'], [1, 2, 3]))) {
            // Optional campaign & phone
            if (!empty($postData['campaign'])) {
                $claims['campaign'] = $postData['campaign'];
            }

            if (!empty($postData['phone'])) {
                $claims['phone'] = $postData['phone'];
            }

            if (!empty($this->uin)) {
                $claims['uin'] = $this->uin;
            }

            if (!empty($this->userId)) {
                $claims['userid'] = $this->userId;
            }
        } else {
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

    public function getUserRoleNameAttribute()
    {
        return $this->roles()->first()?->name;
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
            //->betweenUsers(auth()->id(), $this->id)
            ->where('receiver_id', auth()->user()->id)
            ->where('user_id', $this->id)
            ->count();
    }
}
