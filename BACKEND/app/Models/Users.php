<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JWTSubject
{
    use HasFactory,Notifiable;
    
    protected $table = 'users';

    protected $fillable = [
        "tenant_id",
        "first_name",
        "last_name",
        'username',
        'email',
        'password',
        'phone',
        'phone_password',
        "domain",
        "mobile",
        "type"
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
            'role_id' => $this->role_id
        ];

        // Conditionally add phone to JWT claims if flag == 2
        if ($this->addPhoneToJWT) {
            $claims['phone'] = $this->phone;
        }

        return $claims;
    }
}
