<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsProvider extends Model
{
    protected $table = "providers";

    protected $fillable = [
        'name',
        'status',
        'type'
    ];

    public function configurations()
    {
        return $this->hasMany(ApiConfiguration::class, 'provider_id', 'id');
    }
}
