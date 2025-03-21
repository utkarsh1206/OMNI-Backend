<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SupportTicketNotification extends Model
{
    use HasFactory;
    protected $table = 'support_ticket_notification';

 
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
}
