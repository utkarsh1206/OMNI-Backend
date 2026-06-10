<?php

namespace App\Services;

use App\Models\ApiButtonConfigMapping;

class ExternalApiTargetResolverService
{
    public function isFieldApiEnabled(): bool
    {
        $value = config('omni.FIELD_API_ENABLED', false);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function resolve(string $buttonKey): array
    {
        if ($this->isFieldApiEnabled()) {
            return $this->resolveFromApiConfigMappings($buttonKey);
        }

        return $this->resolveFromEnv();
    }

    private function resolveFromApiConfigMappings(string $buttonKey): array
    {
        $mappings = ApiButtonConfigMapping::query()
            ->with('apiConfig:id,name,base_url,endpoint,method,status')
            ->where('button_key', $buttonKey)
            ->where('is_active', true)
            ->whereHas('apiConfig', function ($q) {
                $q->where('status', true);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $mappings->map(function ($row) {
            return [
                'target_id' => 'cfg:' . $row->api_config_id,
                'source' => 'api_config',
                'label' => $row->display_name ?: ($row->apiConfig->name ?? 'API Config ' . $row->api_config_id),
                'method' => $row->apiConfig->method ?? null,
                'url' => $row->apiConfig ? $row->apiConfig->getFullUrl() : null,
            ];
        })->toArray();
    }

    private function resolveFromEnv(): array
    {
        $indexes = $this->extractNumberedIndexes('EXTERNAL_API_URL_');
        $targets = [];

        foreach ($indexes as $index) {
            $url = env('EXTERNAL_API_URL_' . $index);
            $apiKey = env('EXTERNAL_API_KEY_' . $index);

            if ($this->isFilled($url)) {
                $targets[] = [
                    'target_id' => 'env:' . $index,
                    'source' => 'env',
                    'label' => 'External API ' . $index,
                    'method' => 'POST',
                    'url' => $url,
                    'has_api_key' => $this->isFilled($apiKey),
                ];
            }
        }

        if (!empty($targets)) {
            return $targets;
        }

        $legacyUrl = env('EXTERNAL_API_URL');
        $legacyApiKey = env('EXTERNAL_API_KEY');

        if ($this->isFilled($legacyUrl)) {
            return [[
                'target_id' => 'env:default',
                'source' => 'env',
                'label' => 'External API',
                'method' => 'POST',
                'url' => $legacyUrl,
                'has_api_key' => $this->isFilled($legacyApiKey),
            ]];
        }

        return [];
    }

    private function extractNumberedIndexes(string $prefix): array
    {
        $keys = array_merge(array_keys($_ENV), array_keys($_SERVER));
        $indexes = [];

        foreach ($keys as $key) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\\d+)$/', $key, $matches)) {
                $indexes[] = (int) $matches[1];
            }
        }

        $indexes = array_values(array_unique($indexes));
        sort($indexes);

        return $indexes;
    }

    private function isFilled($value): bool
    {
        return !is_null($value) && trim((string) $value) !== '';
    }
}
