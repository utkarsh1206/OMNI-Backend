<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class CronConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['command_signature','schedule_type','schedule_expression','number_of_day','status'];
}
