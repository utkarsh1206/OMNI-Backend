<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpClientService
{
    public function send(
        string $method,
        string $url,
        array $headers = [],
        array $query = [],
        array $body = [],
        int $timeout = 30,
        int $retries = 0
    ): array {
        $request = Http::withHeaders($headers)
            ->timeout($timeout)
            ->retry($retries, 100);

        if (!empty($query)) {
            $request->withQueryParameters($query);
        }

        $response = match (strtoupper($method)) {
            'GET'    => $request->get($url),
            'POST'   => $request->post($url, $body),
            'PUT'    => $request->put($url, $body),
            'DELETE' => $request->delete($url, $body),
        };

        return [
            'status' => $response->status(),
            'body'   => $response->json() ?? [],
            'error'  => $response->failed() ? $response->body() : null,
        ];
    }
}
