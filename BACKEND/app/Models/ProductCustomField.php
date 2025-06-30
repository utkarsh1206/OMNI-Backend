<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCustomField extends Model
{
    protected $fillable = [
        'product_id',
        'field_name',
        'field_label',
        'field_type',
        'description',
        'options',
        'mandatory',
        'calling_authority',
        'show_authority',
        'agent_view',
        'field_order',
        'field_help',
        'field_size',
        'field_max_length',
        'search_filter',
        'editable',
        'default_value',
        'value_masked',
        'duplicate_check',
        'status',
        'field_column',
        'section_name',
    ];

    /**
     * Get the product that owns this custom field.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
