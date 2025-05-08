<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_size',
        'user_id',
        'type',
    ];

    protected $hidden = ['user'];
    
    protected $appends = ['username', 'full_file_path'];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor to get the username from related user
     */
    public function getUsernameAttribute()
    {
        return $this->user ? $this->user->username : null;
    }

    /**
     * Accessor to get the full file path
     */
    public function getFullFilePathAttribute()
    {
        return url('storage'.DIRECTORY_SEPARATOR.'exports'.DIRECTORY_SEPARATOR.$this->file_name); 
    }
}
