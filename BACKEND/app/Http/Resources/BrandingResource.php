<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'footer_content' => $this->footer_content,
            'footer_url' => $this->footer_url,
            'tenant_logo' => $this->tenant_logo,
        ];
    }
}