<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use HasFactory;
    protected $primaryKey = 'extension';
    public $incrementing = false;
    protected $fillable = [
        'extension',
        'login',
        'pass',
        'status',
        'local_gmt',
        'dialplan_number',
        'voicemail_id',
        'active',
    ];
}
