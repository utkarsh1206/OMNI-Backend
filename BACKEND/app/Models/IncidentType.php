<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'category_id', 'priority_id', 'sla_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function department()
    {
        return $this->category->belongsTo(Department::class);
    }

    public function slaSetting()
    {
        return $this->belongsTo(SlaSetting::class, 'sla_id');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class)->select('id', 'name');
    }

    public function getPriorityNameAttribute()
    {
        return $this->priority ? $this->priority->name : null;
    }
}
