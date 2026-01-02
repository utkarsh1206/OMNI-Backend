<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'assignment_history';

    protected $fillable = [
        'ticket_id',
        'assigned_to_id',
        'assigned_by_id',
        'assignment_method',
        'rule_id',
        'reason',
    ];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function rule()
    {
        return $this->belongsTo(AssignmentRule::class, 'rule_id');
    }
}
