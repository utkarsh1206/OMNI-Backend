<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Request;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory,Notifiable;
    
    protected $table = 'users';

    protected $fillable = [
        "tenant_id",
        "type",
        "first_name",
        "last_name",
        "email",
        "username",
        "password",
        "phone",
        "phone_password",
        "domain",
        "mobile",
        "status",
        "is_delete",
        "role_id",
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
        $claims = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'domain' =>  $this->domain,
        ];

        // Conditionally add phone to JWT claims if flag == 2
        if ($this->addPhoneToJWT) {
            $claims['phone'] = $this->phone;
        }

        return $claims;
    }
}
