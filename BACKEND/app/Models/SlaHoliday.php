<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlaHoliday extends Model
{
    use HasFactory;

    protected $table = 'sla_holidays';

    protected $fillable = [
        'sla_id',
        'holiday_date',
        'description',
    ];
}
