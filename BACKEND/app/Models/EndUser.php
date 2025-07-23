<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class EndUser extends Authenticatable implements JWTSubject
{
    use HasFactory;
    protected $fillable = ['mobile', 'email'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'type' => 'enduser'
        ];
    }
}
