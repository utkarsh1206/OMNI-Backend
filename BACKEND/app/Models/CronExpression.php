<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronExpression extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_type','schedule_expression'];
}
