<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlaOffDay extends Model
{
    use HasFactory;

    protected $table = 'sla_off_days';

    protected $fillable = [
        'sla_id',
        'day',
    ];
}
