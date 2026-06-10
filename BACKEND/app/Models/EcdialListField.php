<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcdialListField extends Model
{
    use HasFactory;

    protected $table = 'ecdial_lists_fields';

    public $timestamps = true;

    private const API_FIELDS = [
        'uses_api',
        'api_config_id',
    ];

    protected $fillable = [
        "id",
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
        "uses_api",
        "api_config_id",
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!config('omni.FIELD_API_ENABLED')) {
            $this->fillable = array_values(array_diff($this->fillable, self::API_FIELDS));
        }
    }

    // Define the relationship to the EcdialList model
    public function list()
    {
        return $this->belongsTo(EcdialLists::class, 'list_id', 'list_id');
        // The 'list_id' foreign key links back to the EcdialList's 'list_id' primary key
    }

    // The ApiConfig entry whose endpoint will supply options for this field
    public function apiConfig(): BelongsTo
    {
        return $this->belongsTo(ApiConfig::class, 'api_config_id');
    }

    // Multi-parameter dependency mappings for this field
    public function dependencyMappings(): HasMany
    {
        return $this->hasMany(EcdialListFieldDependency::class, 'child_field_name', 'field_name')
            ->orderBy('sort_order');
    }
}
