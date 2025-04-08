<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubModule extends Model
{
    protected $fillable = [
        'module_id',
        'name'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
