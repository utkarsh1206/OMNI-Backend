<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'owner_id',
        'created_by',
        'type',
    ];

    public function owner()
    {
        return $this->belongsTo(Users::class, 'owner_id');
    }

    public function participants()
    {
        return $this->hasMany(GroupParticipant::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
