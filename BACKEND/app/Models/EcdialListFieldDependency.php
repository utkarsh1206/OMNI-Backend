<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EcdialListFieldDependency extends Model
{
    use HasFactory;

    protected $table = 'ecdial_list_field_dependencies';

    protected $fillable = [
        'list_id',
        'child_field_name',
        'parent_field_name',
        'param_name',
        'param_type',
        'sort_order',
    ];

    public function childFieldQuery(): Builder
    {
        return EcdialListField::query()
            ->where('list_id', $this->list_id)
            ->where('field_name', $this->child_field_name);
    }

    public function parentFieldQuery(): Builder
    {
        return EcdialListField::query()
            ->where('list_id', $this->list_id)
            ->where('field_name', $this->parent_field_name);
    }
}
