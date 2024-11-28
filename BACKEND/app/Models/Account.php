<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $table = 'accounts';

    // public $timestamps = false;
    
    protected $fillable = [
        'product_id',
        'campaign_id',
        'name',
        'mobile',
        'website',
        'industry_id',
        'business_type_id',
        'tax_number',
        'country_id',
        'state_id',
        'city_id',
        'pincode',
        'address',
        'owner_id',
        'description',
        'product_deployed_type',
        'neox_integration',
        'phone_type',
        'phone_type_comment',
        'pstn_line',
        'pstn_ports_qty',
        'isp_details',
        'did_number_details',
        'pilot_number_detail',
        'extension_range_detail',
        'server_serialnumber',
        'server_ip',
        'server_mac',
        'license_key',
        'no_of_users',
        'account_expiry_date',
        'license_detail',
        'company_name',
        'contact_person_first_name',
        'contact_person_last_name',
        'contact_no',
        'landline_no',
        'company_email',
    ];
}
