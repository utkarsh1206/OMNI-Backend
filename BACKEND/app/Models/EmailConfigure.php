<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailConfigure extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = ['product', 'department'];

    protected $appends = ['product_name', 'depart_name'];

    protected $fillable = [
        'type',
        'product_id',
        'department_id',
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : null;
    }

    public function getDepartNameAttribute()
    {
        return $this->department ? $this->department->name : null;
    }
}
