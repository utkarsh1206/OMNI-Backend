<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcdialEvaluationQuestion extends Model
{
    protected $fillable = [
        'question', 
        'question_type',
        'answers',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    // Relationship with creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'username');
    }

    // Relationship with updater
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->select('id', 'username');
    }
}
