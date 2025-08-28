<?php

namespace App\Services;

use App\Models\ApiEndpoint;
use App\Models\ApiMapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ApiImportService
{
    public function importData(int $listId, int $endpointId, array $payload = []): array
    {
        // generateLogs('info', 'Starting data import');
        try {
            // generateLogs('info', 'Fetching endpoint and mappings');
            $endpoint = ApiEndpoint::where('id', $endpointId)
                ->where('list_id', $listId)
                ->firstOrFail();

            $mappings = ApiMapping::where('endpoint_id', $endpoint->id)->get();

            // generateLogs('info', 'Fetched mappings for endpoint ' . $endpoint->id);
            if ($mappings->isEmpty()) {
                generateLogs('info', 'No mappings defined for endpoint ' . $endpoint->id);
                return ['status' => 'error', 'message' => 'No mappings defined for this endpoint.'];
            }

            // generateLogs('info', 'Building request for endpoint ' . $endpoint->id);
            $client = Http::withOptions(['verify' => false]); // allow self-signed SSL
            // generateLogs('info', 'Sending request to ' . $endpoint->url);

            // Apply authentication if provided
            if ($endpoint->auth_config) {
                // $authConfig = json_decode($endpoint->auth_config, true);
                $authConfig = $endpoint->auth_config;

                if (!empty($authConfig['type'])) {
                    switch (strtolower($authConfig['type'])) {
                        case 'apikey':
                            $client = $client->withHeaders([
                                'Authorization' => 'Apikey ' . $authConfig['token'],
                            ]);
                            break;

                        case 'bearer':
                            $client = $client->withToken($authConfig['token']);
                            break;

                        case 'api_key':
                            if (($authConfig['in'] ?? '') === 'header') {
                                $client = $client->withHeaders([
                                    $authConfig['key'] => $authConfig['value'],
                                ]);
                            } elseif (($authConfig['in'] ?? '') === 'query') {
                                $payload[$authConfig['key']] = $authConfig['value'];
                            }
                            break;

                        case 'basic':
                            $client = $client->withBasicAuth(
                                $authConfig['username'],
                                $authConfig['password']
                            );
                            break;
                    }
                }
            }

            // generateLogs('info', 'after Case Sending request to ' . $endpoint->url);
            // Send request
            if ($endpoint->method === 'POST') {
                if ($endpoint->payload_type === 'json') {
                    $response = $client->post($endpoint->url, $payload);
                } else { // query
                    $response = $client->asForm()->post($endpoint->url, $payload);
                }
            } else { // GET
                if ($endpoint->payload_type === 'query' && !empty($payload)) {
                    $response = $client->get($endpoint->url, $payload);
                } else {
                    $response = $client->get($endpoint->url);
                }
            }
            // generateLogs('info', 'after sending request to ' . $endpoint->url);

            if (!$response->successful()) {
                generateLogs('error', "API Request failed for endpoint {$endpoint->id}: " . $response->body());
                return ['status' => 'error', 'message' => 'API request failed.'];
            }

            $data = $response->json();
            $records = isset($data[0]) ? $data : [$data];

            $insertData = [];
            $failedCount = 0;

            foreach ($records as $record) {
                $row = [];

                foreach ($mappings as $map) {
                    $value = $this->getValueFromKey($record, $map->api_key);

                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    if (Schema::hasColumn("custom_lead_{$listId}", $map->column_name)) {
                        $row[$map->column_name] = $value;
                    } else {
                        generateLogs('warning', "Column {$map->column_name} not found in custom_lead_{$listId}");
                        $failedCount++;
                    }
                }

                $row['created_at'] = now();
                $row['updated_at'] = now();
                $row['omni_lead_source'] = 'api-' . $endpoint->id;

                if (!empty($row)) {
                    $insertData[] = $row;
                }
            }

            if (!empty($insertData)) {
                DB::transaction(function () use ($listId, $insertData) {
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        DB::table("custom_lead_{$listId}")->insert($chunk);
                    }
                });
            }

            return [
                'status' => 'success',
                // 'message' => count($insertData) . ' records inserted.',
                'message' => 'Data inserted successfully.',
                'data' => [
                    'failed_count' => $failedCount,
                    'imported_data' => $insertData,
                    'inserted_count' => count($insertData)
                ]
            ];
        } catch (\Exception $e) {
            generateLogs('error', "Import failed for endpoint {$endpointId}: ", $e->getMessage());
            return ['status' => 'error', 'message' => 'Import failed.'];
        }
    }

    private function getValueFromKey(array $data, string $key)
    {
        $keys = explode('.', $key);
        foreach ($keys as $k) {
            if (is_array($data) && array_key_exists($k, $data)) {
                $data = $data[$k];
            } else {
                return null;
            }
        }
        return $data;
    }
}
