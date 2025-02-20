<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConfigure extends Model
{
    use SoftDeletes;
    protected $table = 'email_configures';
    protected $fillable = [
        'type',
        'product_id',
        'campaign_id',
        'inbound_id',
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_password',
        'default_type',
        'default_status',
        'imap_host',
        'imap_port',
        'imap_user',
        'imap_password',
        'sender_name',
        'sender_email',
        'assign_method',
        'signature',
    ];
}
