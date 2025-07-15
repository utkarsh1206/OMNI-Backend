<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketNotification extends Model
{
    use HasFactory;

    protected $hidden = ['ticketStatus'];

    protected $appends = ['status_name'];

    protected $fillable = [
        'type',
        'status',
        'comment',
        'date',
        'support_ticket_id',
        'created_by',
        'icon',
        'color'
    ];

    // public function getCreatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->format('Y-m-d H:i:s');
    // }

    // public function getUpdatedAtAttribute($value)
    // {
    //     return Carbon::parse($value)->format('Y-m-d H:i:s');
    // }

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedCommentAttribute()
    {
        $comment = $this->attributes['comment'];

        // Match pattern: Change ticket status|priority|type to: ID
        if (preg_match('/Change ticket (status|priority|type) to:\s*(\d+)/i', $comment, $matches)) {
            $field = strtolower($matches[1]);
            $id = (int) $matches[2];
            $name = null;

            switch ($field) {
                case 'status':
                    $name = SupportTicketStatus::find($id)?->name;
                    break;
                case 'priority':
                    $name = Priority::find($id)?->name;
                    break;
                case 'type':
                    $name = TicketType::find($id)?->type;
                    break;
            }

            if ($name) {
                return "Change ticket {$field} to: {$name}";
            }
        }

        // Fallback to original if no match
        return $comment;
    }

    public function getFormattedTypeAttribute()
    {
        $type = $this->attributes['type'];

        if (is_numeric($type)) {
            return SupportTicketStatus::find($type)?->name ?? $type;
        }

        return $type;
    }

    public function ticketStatus()
    {
        return $this->belongsTo(SupportTicketStatus::class, 'status');
    }

    public function getStatusNameAttribute()
    {
        return $this->ticketStatus ? $this->ticketStatus->name : null;
    }
}
