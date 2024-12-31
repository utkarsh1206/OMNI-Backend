<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supportticket extends Model
{
    protected $table = 'supportticket';

    protected $fillable = [
        //"tenant_id",
        "id",
        "product_id",
        "campaign_id",
        //"inbound_group_id",
        "contact_id",
        "assign_to_id",
        "default_type",
        "default_priority",
        "default_status",
        "subject",
        "departments",
        "to_emails",
        "cc",
        "bcc",
        "description",
        "attachment",
        //"status",
        "incident_id",
        "is_delete",
        "support_ticket_id"
    ];
}
