<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlaSetting extends Model
{
    use HasFactory;

    protected $table = 'sla_settings';

    protected $fillable = [
        'name',
        'description',
        'is_24_hours',
        'start_time',
        'end_time',
    ];

    public function offDays()
    {
        return $this->hasMany(SlaOffDay::class, 'sla_id');
    }

    public function holidays()
    {
        return $this->hasMany(SlaHoliday::class, 'sla_id');
    }

    public function targets()
    {
        return $this->hasMany(SlaTarget::class, 'sla_id');
    }
}
