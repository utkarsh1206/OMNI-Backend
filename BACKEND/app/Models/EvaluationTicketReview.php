<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationTicketReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'bunch_name',
        'questions',
        'selected_answer',
        'comment',
        'ticket_rating',
        'created_by',
    ];
}
