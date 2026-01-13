<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_name',
        'selected_columns',
        'from_email',
        'to_emails',
        'cc_emails',
        'bcc_emails',
        'subject',
        'body',
        'frequency',
        'scheduled_time',
        'day_of_week',
        'data_start_time',
        'data_end_time',
        'timezone',
        'is_active'
    ];

    protected $casts = [
        'selected_columns' => 'array',
        'to_emails' => 'array',
        'cc_emails' => 'array',
        'scheduled_time' => 'datetime:H:i',
        'data_start_time' => 'datetime:H:i',
        'data_end_time' => 'datetime:H:i',
    ];
}
