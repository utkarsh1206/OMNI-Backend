<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Supportticket extends Model
{
    protected $table = 'supportticket';

    protected $fillable = [
        //"tenant_id",
        "id",
        "product_id",
        "campaign_id",
        //"inbound_group_id",
        "account_id",
        "contact_id",
        "assign_to_id",
        "default_type",
        "default_priority",
        "default_status",
        "subject",
        "department_id",
        "to_emails",
        "cc",
        "bcc",
        "description",
        "attachment",
        //"status",
        "incident_id",
        "is_delete",
        "support_ticket_id",
        "first_response_time",
        "alert_before_response_notification_time",
        "resolved_time",
        "alert_before_resolve_notification_time",
        "is_escalated"
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assign_to_id');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'resolved_time' => 'datetime',
        'first_response_time' => 'datetime',
    ];

    public function notifications()
    {
        return $this->hasMany(SupportTicketNotification::class, 'support_ticket_id', 'id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(Users::class, 'assign_to_id');
    }

    public function incidentType()
    {
        return $this->belongsTo(IncidentType::class, 'incident_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
