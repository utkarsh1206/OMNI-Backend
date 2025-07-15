<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationQuestionUsageType extends Model
{
    use HasFactory;

    protected $fillable = ['usage_type', 'status'];

    public static function getIdByName($name): ?int
    {
        return self::where('usage_type', $name)->value('id');
    }
}
