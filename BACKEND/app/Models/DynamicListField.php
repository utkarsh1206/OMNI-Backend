<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicListField extends Model
{
    use HasFactory;

    protected $table = 'ecdial_lists_fields';

    public $timestamps = true;

    protected $fillable = [
        // "id",
        "list_id",
        "field_name",
        "field_label",
        "field_type",
        "description",
        "options",
        "mandatory",
        "calling_authority",
        "show_authority",
        "agent_view",
        "field_order",
        "field_help",
        "field_size",
        "field_max_length",
        "search_filter",
        "editable",
        "default_value",
        "value_masked",
        "duplicate_check",
        "status",
        "field_column",
        "group_name",
        "send_to_crm",
    ];

    // Define the relationship to the EcdialList model
    public function list()
    {
        return $this->belongsTo(EcdialLists::class, 'list_id', 'list_id');
        // The 'list_id' foreign key links back to the EcdialList's 'list_id' primary key
    }
}
