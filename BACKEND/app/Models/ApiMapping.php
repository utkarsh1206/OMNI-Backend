<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiMapping extends Model
{
    protected $fillable = ['endpoint_id', 'api_key', 'column_name'];

    public function endpoint()
    {
        return $this->belongsTo(ApiEndpoint::class, 'endpoint_id');
    }
}
