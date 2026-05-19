# API Implementation Documentation

This document describes the centralized API management and authentication system implemented in the OMNI Backend.

## Overview

The system provides a robust way to manage external API integrations, supporting various authentication methods, token lifecycle management (caching and refreshing), and detailed request/response logging.

### Key Features
- **Centralized API Configs**: Manage base URLs, endpoints, methods, and default parameters.
- **Runtime Dynamic Parameters**: Use `{{placeholder}}` syntax in any stored field (URL path, body, query params, headers) and supply values at call time.
- **Flexible Authentication**: 
    - **Inline Auth**: Simple API Key or Basic Auth defined directly on the API config.
    - **Shared Auth Providers**: Reusable authentication configurations (OAuth2, Bearer, etc.) shared across multiple APIs.
- **Token Management**: Automatic token retrieval, caching, and background refreshing on expiry.
- **Logging**: Comprehensive audit trail of all external API calls.
- **Resilience**: Support for timeouts and retries.

---

## Architecture

### Database Schema

| Table | Model | Description |
|---|---|---|
| `auth_providers` | `AuthProvider` | Defines how to authenticate (Token URL, Credentials, Expiry rules). |
| `api_configs` | `ApiConfig` | Defines specific endpoints and links to an optional `AuthProvider`. |
| `api_tokens` | `ApiToken` | Stores active access/refresh tokens for `AuthProviders`. |
| `api_logs` | `ApiLog` | Logs every request, response, and status code. |

### Core Services

1.  **`ApiCallerService`**: The primary entry point. Signature: `call(ApiConfig $apiConfig, array $override = [], array $params = [])`. Orchestrates the full request lifecycle:
    - Resolves `{{placeholder}}` tokens in headers, query params, body, and URL using `$params`.
    - Resolves authentication via `AuthResolverService`.
    - Executes the HTTP request via `HttpClientService`.
    - Handles automatic token refresh and retry on 401/expiry.
    - Persists the result in `ApiLog`.
2.  **`AuthResolverService`**: Injects credentials into headers, query parameters, or the request body based on the configuration.
3.  **`TokenManagerService`**: Handles the logic of fetching new tokens from external providers and updating the `api_tokens` table.
4.  **`HttpClientService`**: A wrapper around Laravel's `Http` facade to provide consistent timeout, retry, and error handling.

---

## Authentication Strategies

### 1. Inline Authentication
Used for simple APIs where credentials are unique to a single endpoint.
- **Types**: `none`, `api_key`, `basic`.
- **Location**: Credentials can be placed in `header`, `query`, or `body`.

### 2. Auth Providers
Used for complex authentication or when credentials are shared across multiple endpoints (e.g., a CRM or ERP system).
- **Supported Types**: `bearer_token`, `oauth2`, `hmac`, `jwt`, `api_key`, `basic`.
- **Token Lifecycle**:
    - **Caching**: Tokens are stored in `api_tokens` and reused until they expire.
    - **Refresh**: If a request fails with an expiry indicator (defined in `token_expiry_rules`), the system automatically refreshes the token and retries the call.
    - **Mapping**: Flexible JSON mapping allows extracting tokens and expiry times from any nested JSON response.

---

## Usage Guide

### Calling an API via Code

To execute an API call, use the `ApiCallerService`:

```php
use App\Models\ApiConfig;
use App\Services\ApiCallerService;

$apiCaller = app(ApiCallerService::class);
$apiConfig = ApiConfig::where('name', 'ExternalService')->first();

// Optional: Override headers, query, or body at runtime
$overrides = [
    'body' => ['key' => 'value'],
    'query' => ['page' => 1]
];

// Optional: Resolve {{placeholder}} tokens in stored fields at runtime
$params = [
    'user_id' => 42,
    'tenant'  => 'acme',
];

$response = $apiCaller->call($apiConfig, $overrides, $params);

if ($response['status'] === 200) {
    $data = $response['body'];
} else {
    $error = $response['error'];
}
```

### Dynamic Runtime Parameters

Any stored field in `ApiConfig` can contain `{{placeholder}}` tokens. Supply values via the `$params` argument at call time.

**Example stored config:**
```json
{
  "name": "Get User Orders",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/users/{{user_id}}/orders",
  "method": "GET",
  "headers": { "X-Tenant": "{{tenant}}" },
  "query_params": { "status": "{{status}}" }
}
```

**Call:**
```php
$response = $apiCaller->call($apiConfig, [], [
    'user_id' => 42,
    'tenant'  => 'acme',
    'status'  => 'active',
]);
// Outgoing: GET https://api.example.com/v1/users/42/orders?status=active
// Headers:  X-Tenant: acme
```

**Rules:**
- Placeholders use double curly-brace syntax: `{{key}}`.
- All four locations are supported: `endpoint` (URL path), `body`, `query_params`, `headers`.
- Nested JSON arrays/objects are resolved recursively.
- Unresolved placeholders (no matching key supplied) are left as-is.
- Resolution runs **after** override merging but **before** auth injection, so auth tokens are always cleanly applied on top.

### Configuring an API (REST API)

#### Store API Config
`POST /api/api-configs`
```json
{
  "name": "Get User Profile",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/profile",
  "method": "GET",
  "auth_type": "api_key",
  "auth_location": "header",
  "auth_input": "X-API-KEY: my-secret-key"
}
```

#### Store Auth Provider
`POST /api/auth-providers`
```json
{
  "name": "Main OAuth Provider",
  "type": "oauth2",
  "token_url": "https://auth.example.com/token",
  "method": "POST",
  "request_config": {
    "body": {
      "grant_type": "client_credentials",
      "client_id": "xxx",
      "client_secret": "yyy"
    }
  },
  "response_mapping": {
    "access_token": "data.token",
    "expires_in": "data.ttl"
  },
  "expiry_type": "seconds"
}
```

---

## Development Notes

### Inline Auth Parsing
The `ApiConfigController` includes a helper `parseKeyValueInput` that allows users to provide credentials in a simple `Key: Value` or `Key=Value` format via the `auth_input` field, which is then converted to a JSON `auth_config` structure.

### Token Invalidation
When an `AuthProvider`'s configuration is updated (e.g., new credentials or URL), the `AuthProviderController` automatically deletes any existing cached tokens to ensure the next request triggers a fresh authentication flow.
