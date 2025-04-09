<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'product_id',
        'account_id',
        'name_prefix',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'owner_id',
        'country_id',
        'state_id',
        'city_id',
        'pincode',
        'address',
        'gender',
        'description',
    ];
}
