<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcdialListFieldDependencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'parent_field_name' => $this->parent_field_name,
            'param_name' => $this->param_name,
            'param_type' => $this->param_type,
            'sort_order' => $this->sort_order,
        ];
    }
}
