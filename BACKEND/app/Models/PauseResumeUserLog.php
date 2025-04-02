<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PauseResumeUserLog extends Model
{
    protected $table = 'pause_resume_user_logs';

    // The primary key for the model.
    protected $primaryKey = 'id';

    // Indicates if the model should be timestamped.
    public $timestamps = true;

    // The attributes that are mass assignable.
    protected $fillable = [
        'company_id',
        'product_id',
        'campaign_id',
        'user_id',
        'pause_code_id',
        'user_status',
        'pause_time',
        'resume_time',
        'total_pause_time',
        'total_resume_time',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
