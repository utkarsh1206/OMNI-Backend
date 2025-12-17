<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadImportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_token',
        'file_name',
        'file_type',
        'import_success_count',
        'import_fail_count',
    ];
}
