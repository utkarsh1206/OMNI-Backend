<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenants extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        "username",
        "first_name",
        "last_name",
        "email",
        "password",
        "mobile",
        "domain_name",
        "company_code",
        "company_name",
        "server_serialnumber",
        "server_ip",
        "server_mac",
        "domain_url",
        "tenant_role_id",
        "title",
        "footer_content",
        "footer_url",
        "tenant_logo"
    ];
}
