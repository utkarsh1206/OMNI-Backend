<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'status', 'master_id'];

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'default_priority');
    }

    public function slaTarget()
    {
        return $this->hasMany(SlaTarget::class, 'priority_type');
    }
}
