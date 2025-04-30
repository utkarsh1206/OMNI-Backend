<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationCallReview extends Model
{
    public $timestamps = true;

    // Allow mass assignment for these attributes
    protected $fillable = [
        'callerid',
        'user_id',
        'campaign_id',
        'bunch_name',
        'questions',
        'selected_answer',
        'comment',
        'call_rating',
        'created_by',
    ];
}
