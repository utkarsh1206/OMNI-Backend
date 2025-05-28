<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantLicense extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'tenant_licenses';

    protected $appends = ['tenant_name'];

    protected $fillable = [
        'id',
        'tenant_id',
        'start_date',
        'expire_date',
        'agent_ct',
        'live_agent_ct',
        'manager_ct',
        'created_at',
        'updated_at'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getTenantNameAttribute()
    {
        return $this->tenant ? $this->tenant->username : null;
    }
}
