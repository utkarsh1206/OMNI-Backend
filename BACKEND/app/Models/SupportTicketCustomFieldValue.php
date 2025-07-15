<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketCustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'support_ticket_custom_field_values';

    protected $fillable = [
        'support_ticket_id',
        'product_id',
        'field_name',
        'field_value',
    ];


    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Get the product custom field definition associated with this value.
     *
     * This relationship uses the composite key (product_id, field_name)
     * to link to the product_custom_fields table.
     */
    public function productCustomField(): BelongsTo
    {
        return $this->belongsTo(
            ProductCustomField::class,
            ['product_id', 'field_name'],
            ['product_id', 'field_name']
        );
    }

    /**
     * You might also want to add a mutator/accessor for `field_value`
     * to automatically handle serialization/deserialization if the `field_type`
     * from `productCustomField` indicates a complex data type (e.g., array for 'multi', 'checkbox').
     * For example, if field_value stores JSON string for 'multi' type:
     */
    /**
     * public function getFieldValueAttribute($value)
     * {
     *     // Assuming 'productCustomField' relation is loaded
     *     if ($this->productCustomField && in_array($this->productCustomField->field_type, ['multi', 'checkbox'])) {
     *         return json_decode($value, true);
     *     }
     *     return $value;
     * }

     * public function setFieldValueAttribute($value)
     * {
     *     // Assuming 'productCustomField' relation is loaded
     *     if ($this->productCustomField && in_array($this->productCustomField->field_type, ['multi', 'checkbox']) && is_array($value)) {
     *         $this->attributes['field_value'] = json_encode($value);
     *     } else {
     *         $this->attributes['field_value'] = $value;
     *     }
     * }
     */
}
