<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailDetail extends Model
{
    use HasFactory;

    protected $appends = ['status_name', 'priority_name'];
    protected $hidden = ['priorityData', 'ticketStatus'];

    // Add table name if it doesn't follow the default naming convention
    protected $fillable = [
        'date',
        'user_id',
        'type',
        'priority',
        'status',
        'to',
        'cc',
        'bcc',
        'body',
        'attachment',
        'supportticket_id'
    ];

    public function priorityData()
    {
        return $this->belongsTo(Priority::class, 'priority', 'id');
    }

    public function ticketStatus()
    {
        return $this->belongsTo(SupportTicketStatus::class, 'status', 'id');
    }

    public function getStatusNameAttribute()
    {
        return $this->ticketStatus?->name;
    }

    public function getPriorityNameAttribute()
    {
        return $this->priorityData?->name;
    }
}
