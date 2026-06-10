<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcdialListFieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        if (config('omni.FIELD_API_ENABLED')) {
            $data['api_config'] = $this->whenLoaded('apiConfig');
            $data['dependency_mappings'] = EcdialListFieldDependencyResource::collection(
                $this->whenLoaded('dependencyMappings')
            );
        }

        return $data;
    }
}
