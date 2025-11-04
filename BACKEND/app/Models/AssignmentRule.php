<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'incident_type_id',
        'priority_id',
        'sla_id',
        'method',
        'parameters',
        'enabled',
        'priority_order',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'enabled' => 'boolean',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function incidentType()
    {
        return $this->belongsTo(IncidentType::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function sla()
    {
        return $this->belongsTo(SlaSetting::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function history()
    {
        return $this->hasMany(AssignmentHistory::class, 'rule_id');
    }
}
