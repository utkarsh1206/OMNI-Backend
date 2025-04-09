<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'priority', 'category_id', 'priority', 'sla_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->category->belongsTo(Department::class);
    }

    public function slaSettings()
    {
        return $this->belongsTo(SlaSetting::class, 'sla_id');
    }
}
