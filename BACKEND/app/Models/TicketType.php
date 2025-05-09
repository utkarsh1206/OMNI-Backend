<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'description', 'status', 'master_id'];

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'default_types');
    }
}
