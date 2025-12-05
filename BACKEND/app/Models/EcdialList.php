<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcdialList extends Model
{
    protected $table = 'ecdial_list';
    protected $primaryKey = 'lead_id';
    
    protected $fillable = [
        'entry_date',
        'modify_date',
        'status',
        'new_lead_on_incoming_call',
        'user',
        'vendor_lead_code',
        'alt_dial',
        'source_id',
        'list_id',
        'gmt_offset_now',
        'called_since_last_reset',
        'phone_code',
        'phone_number',
        'title',
        'first_name',
        'middle_initial',
        'last_name',
        'address1',
        'address2',
        'address3',
        'city',
        'state',
        'province',
        'postal_code',
        'country_code',
        'gender',
        'date_of_birth',
        'alt_phone',
        'email',
        'security_phrase',
        'comments',
        'called_count',
        'last_local_call_time',
        'rank',
        'owner',
        'entry_list_id',
        'per_day_maximum_callback_hops_for_fail_calls',
        'per_day_maximum_callback_hops_for_success_calls',
        'global_maximum_callback_hops_for_fail_calls',
        'global_maximum_callback_hops_for_success_calls',
        'area',
        'reference_id',
        'contact_person',
        'entrydate',
        'lastdate',
        'region',
        'lead_priority',
        'customer_dispo_status',
        'last_dialable_time',
    ];
}
