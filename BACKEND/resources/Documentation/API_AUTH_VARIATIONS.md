# API Authentication Variations Documentation

This document outlines all permissible variations for `AuthProvider` and `ApiConfig` based on the current implementation. It provides example payloads for creating these configurations via the API.

---

## 1. Auth Providers (`POST /api/auth-providers`)

Auth Providers are reusable authentication configurations that handle token acquisition, caching, and refresh logic.

### 1.1 Bearer Token / OAuth2 (Token-Based)
Used when you need to fetch a token from a `token_url` and use it as a `Bearer` token.

```json
{
  "name": "Production OAuth2 Provider",
  "type": "oauth2",
  "token_url": "https://auth.example.com/oauth/token",
  "method": "POST",
  "request_config": {
    "headers": {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    "body": {
      "grant_type": "client_credentials",
      "client_id": "YOUR_CLIENT_ID",
      "client_secret": "YOUR_CLIENT_SECRET"
    }
  },
  "response_mapping": {
    "access_token": "access_token",
    "expires_in": "expires_in",
    "refresh_token": "refresh_token"
  },
  "expiry_type": "seconds",
  "token_cache_enabled": true,
  "get_on_each_request": false
}
```

### 1.2 API Key (Provider-Level)
Used when an API key is reused across multiple endpoints and managed centrally.

```json
{
  "name": "Global API Key Provider",
  "type": "api_key",
  "credentials": {
    "key_name": "X-API-KEY",
    "api_key": "your-secret-api-key-here",
    "location": "header"
  }
}
```
*Note: `location` can be `header`, `query`, or `body`.*

### 1.3 Bearer Token (Static / Pre-issued Token)
Used when you already hold a long-lived bearer token and do not need to fetch one dynamically. The token is stored in `credentials` and injected as a `Bearer` header on every request.

```json
{
  "name": "Static Bearer Token Provider",
  "type": "bearer_token",
  "credentials": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  },
  "token_cache_enabled": false,
  "get_on_each_request": false
}
```
*Note: Because there is no `token_url`, `TokenManagerService` is not invoked. Set `token_cache_enabled: false` when you manage token rotation externally.*

### 1.4 Bearer Token / OAuth2 with Timestamp-Based Expiry
Same as 1.1 but the token endpoint returns an absolute `expires_at` timestamp instead of a seconds-based `expires_in`.

```json
{
  "name": "OAuth2 Provider (Timestamp Expiry)",
  "type": "oauth2",
  "token_url": "https://auth.example.com/oauth/token",
  "method": "POST",
  "request_config": {
    "headers": {
      "Content-Type": "application/json"
    },
    "body": {
      "grant_type": "client_credentials",
      "client_id": "YOUR_CLIENT_ID",
      "client_secret": "YOUR_CLIENT_SECRET"
    }
  },
  "response_mapping": {
    "access_token": "data.token",
    "expires_at": "data.expiry_time"
  },
  "expiry_type": "timestamp",
  "token_cache_enabled": true,
  "get_on_each_request": false
}
```
*Note: `response_mapping` values support dot-notation for nested response keys (powered by Laravel's `data_get`).*

### 1.5 OAuth2 with Token Expiry Rules (Response-Based Detection)
Used when the upstream API signals an expired token via a specific HTTP status code or a field in the response body. The system will automatically force-refresh the token and retry the request once.

**By HTTP status code:**
```json
{
  "name": "OAuth2 Provider with 401 Retry",
  "type": "oauth2",
  "token_url": "https://auth.example.com/oauth/token",
  "method": "POST",
  "request_config": {
    "body": {
      "grant_type": "client_credentials",
      "client_id": "YOUR_CLIENT_ID",
      "client_secret": "YOUR_CLIENT_SECRET"
    }
  },
  "response_mapping": {
    "access_token": "access_token",
    "expires_in": "expires_in"
  },
  "expiry_type": "seconds",
  "token_expiry_rules": {
    "status_code": 401
  },
  "token_cache_enabled": true
}
```

**By response body field:**
```json
{
  "name": "OAuth2 Provider with Body-Based Expiry Detection",
  "type": "oauth2",
  "token_url": "https://auth.example.com/oauth/token",
  "method": "POST",
  "request_config": {
    "body": {
      "grant_type": "client_credentials",
      "client_id": "YOUR_CLIENT_ID",
      "client_secret": "YOUR_CLIENT_SECRET"
    }
  },
  "response_mapping": {
    "access_token": "access_token",
    "expires_in": "expires_in"
  },
  "expiry_type": "seconds",
  "token_expiry_rules": {
    "error_key": "error.code",
    "error_value": "TOKEN_EXPIRED"
  },
  "token_cache_enabled": true
}
```
*Note: `error_key` supports dot-notation. The check is performed in `AuthProvider::isTokenExpiredFromResponse()`. Both `status_code` and `error_key`/`error_value` rules can coexist — either condition triggers a refresh.*

### 1.6 OAuth2 — Fetch Token on Every Request
Disables caching entirely. A fresh token is fetched before each API call. Useful for short-lived tokens or debugging.

```json
{
  "name": "OAuth2 No-Cache Provider",
  "type": "oauth2",
  "token_url": "https://auth.example.com/oauth/token",
  "method": "POST",
  "request_config": {
    "body": {
      "grant_type": "client_credentials",
      "client_id": "YOUR_CLIENT_ID",
      "client_secret": "YOUR_CLIENT_SECRET"
    }
  },
  "response_mapping": {
    "access_token": "access_token"
  },
  "token_cache_enabled": true,
  "get_on_each_request": true
}
```
*Note: When `get_on_each_request` is `true`, `shouldRefreshToken()` always returns `true` regardless of the cached token's expiry.*

### 1.7 Basic Auth (Provider-Level)
Used when multiple API configs share the same Basic Auth credentials. Credentials are stored centrally and injected as a `Basic` `Authorization` header.

```json
{
  "name": "Shared Basic Auth Provider",
  "type": "basic",
  "credentials": {
    "username": "admin",
    "password": "s3cretpassword"
  }
}
```
*Note: `basic` at the provider level is schema-supported. Full injection support requires extending `AuthResolverService::resolveProvider()` to handle `basic` type.*

### 1.8 HMAC (Schema Supported)
Registered as a valid `type` in the `auth_providers` table. Store your HMAC signing key and algorithm in `credentials`. Full request-signing logic requires implementation in `AuthResolverService`.

```json
{
  "name": "HMAC Signed Provider",
  "type": "hmac",
  "credentials": {
    "secret_key": "your-hmac-secret",
    "algorithm": "sha256",
    "header_name": "X-Signature"
  }
}
```

### 1.9 JWT (Schema Supported)
Registered as a valid `type`. Store your JWT signing key, algorithm, and any custom claims in `credentials`. Full JWT generation and injection requires implementation in `AuthResolverService`.

```json
{
  "name": "JWT Provider",
  "type": "jwt",
  "credentials": {
    "secret_key": "your-jwt-secret",
    "algorithm": "HS256",
    "issuer": "your-app",
    "audience": "target-api",
    "ttl_seconds": 3600
  }
}
```

---

## 2. API Configurations (`POST /api/api-configs`)

API Configurations define the endpoint and how it should be authenticated (either via an `AuthProvider` or "Inline").

### 2.1 Linked to Auth Provider
Uses a previously created `AuthProvider` by its ID.

```json
{
  "name": "Fetch User Profile",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/profile",
  "method": "GET",
  "auth_type": "external",
  "auth_provider_id": 1,
  "timeout": 30,
  "retry_count": 2
}
```

### 2.2 Inline API Key (Header)
Authenticates using an API key defined directly in the config.

```json
{
  "name": "Weather API",
  "base_url": "https://api.weather.com",
  "endpoint": "/current",
  "method": "GET",
  "auth_type": "api_key",
  "auth_location": "header",
  "auth_input": "X-API-Token: my-secret-token",
  "query_params": {
    "city": "London"
  }
}
```

### 2.3 Inline API Key (Query Parameter)
Authenticates using an API key passed in the URL.

```json
{
  "name": "Maps API",
  "base_url": "https://maps.googleapis.com",
  "endpoint": "/maps/api/distancematrix/json",
  "method": "GET",
  "auth_type": "api_key",
  "auth_location": "query",
  "auth_input": "key=YOUR_GOOGLE_MAPS_KEY"
}
```

### 2.4 Inline Basic Auth
Authenticates using a username and password.

```json
{
  "name": "Internal Legacy API",
  "base_url": "https://legacy.internal.com",
  "endpoint": "/data",
  "method": "POST",
  "auth_type": "basic",
  "auth_input": "username:admin\npassword:secretpassword123"
}
```
*Note: Use either `:` or `=` to separate keys and values in `auth_input`.*

### 2.5 No Authentication
For public endpoints.

```json
{
  "name": "Public Holidays API",
  "base_url": "https://date.nager.at",
  "endpoint": "/api/v3/PublicHolidays/2024/US",
  "method": "GET",
  "auth_type": "none"
}
```

### 2.6 Linked to Provider-Level Basic Auth
Links an API config to an `AuthProvider` of type `basic` (see section 1.7). The `Authorization: Basic …` header is injected automatically.

```json
{
  "name": "Legacy Internal Service",
  "base_url": "https://legacy.internal.com",
  "endpoint": "/reports/daily",
  "method": "GET",
  "auth_type": "external",
  "auth_provider_id": 3,
  "timeout": 60,
  "retry_count": 1
}
```

### 2.7 Linked to Provider-Level API Key
Links to a centrally managed API key provider (see section 1.2). The key location (`header`, `query`, or `body`) is controlled by the provider's `credentials.location`.

```json
{
  "name": "Analytics Dashboard API",
  "base_url": "https://analytics.example.com",
  "endpoint": "/v2/metrics",
  "method": "POST",
  "auth_type": "external",
  "auth_provider_id": 2,
  "body": {
    "range": "last_7_days",
    "metric": "page_views"
  },
  "timeout": 30,
  "retry_count": 0
}
```

### 2.8 API with Static Headers and Query Params
No auth, but requires specific headers and query parameters on every call.

```json
{
  "name": "Currency Exchange API",
  "base_url": "https://api.exchangerate.host",
  "endpoint": "/convert",
  "method": "GET",
  "auth_type": "none",
  "headers": {
    "Accept": "application/json",
    "X-Client-ID": "omni-platform"
  },
  "query_params": {
    "from": "USD",
    "to": "EUR",
    "amount": "1"
  }
}
```

### 2.9 Inline API Key (Request Body)
Passes the API key as a field inside the request body.

```json
{
  "name": "SMS Gateway API",
  "base_url": "https://sms.example.com",
  "endpoint": "/send",
  "method": "POST",
  "auth_type": "api_key",
  "auth_location": "body",
  "auth_input": "api_key=YOUR_SMS_GATEWAY_KEY",
  "body": {
    "to": "+1234567890",
    "message": "Hello from Omni"
  }
}
```

### 2.10 POST Request with Timeout and Retry Overrides
Useful for slow or unreliable upstream services.

```json
{
  "name": "Slow Upstream Service",
  "base_url": "https://slow.thirdparty.com",
  "endpoint": "/process",
  "method": "POST",
  "auth_type": "external",
  "auth_provider_id": 1,
  "headers": {
    "Content-Type": "application/json"
  },
  "body": {
    "job_type": "report"
  },
  "timeout": 120,
  "retry_count": 3
}
```
*Note: `timeout` is in seconds. `retry_count` retries are spaced 100ms apart (controlled by `HttpClientService`).*

### 2.11 Dynamic URL Path Parameters
Embed `{{placeholder}}` tokens in the `endpoint` to build resource-specific URLs at runtime. The placeholders are resolved by the caller via the `$params` argument.

```json
{
  "name": "Get Order Details",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/orders/{{order_id}}",
  "method": "GET",
  "auth_type": "external",
  "auth_provider_id": 1
}
```
**Call (PHP):**
```php
$response = $apiCaller->call($apiConfig, [], ['order_id' => 99]);
// Outgoing URL: https://api.example.com/v1/orders/99
```
**Call (via test endpoint):**
```json
{
  "api_config_id": 5,
  "params": { "order_id": 99 }
}
```

### 2.12 Dynamic Body Parameters
Store a template body with placeholders. Different callers supply different values without creating separate `ApiConfig` records.

```json
{
  "name": "Send Notification",
  "base_url": "https://notify.example.com",
  "endpoint": "/v1/send",
  "method": "POST",
  "auth_type": "external",
  "auth_provider_id": 2,
  "body": {
    "recipient": "{{email}}",
    "subject": "{{subject}}",
    "message": "{{message}}"
  }
}
```
**Call:**
```php
$response = $apiCaller->call($apiConfig, [], [
    'email'   => 'user@example.com',
    'subject' => 'Your order is ready',
    'message' => 'Order #99 has been dispatched.',
]);
```

### 2.13 Dynamic Parameters Across All Locations
Placeholders work in the `endpoint` (URL path), `body`, `query_params`, and `headers` simultaneously. A single `$params` map resolves all of them in one call.

```json
{
  "name": "Tenant-Scoped Search",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/tenants/{{tenant_id}}/search",
  "method": "GET",
  "auth_type": "external",
  "auth_provider_id": 1,
  "headers": {
    "X-Tenant": "{{tenant_id}}",
    "Accept-Language": "{{locale}}"
  },
  "query_params": {
    "q": "{{query}}",
    "page": "{{page}}"
  }
}
```
**Call:**
```php
$response = $apiCaller->call($apiConfig, [], [
    'tenant_id' => 'acme',
    'locale'    => 'en-US',
    'query'     => 'invoices',
    'page'      => 2,
]);
// URL:     https://api.example.com/v1/tenants/acme/search?q=invoices&page=2
// Headers: X-Tenant: acme, Accept-Language: en-US
```
*Note: Unresolved placeholders (keys not present in `$params`) are left as-is in the outgoing request.*

---

## 3. Dynamic Runtime Parameters

The `{{placeholder}}` system allows a single stored `ApiConfig` to serve many different runtime contexts. Placeholders can appear in any of the four request-building locations.

### Placeholder Rules
| Rule | Detail |
| :--- | :--- |
| Syntax | `{{key_name}}` (double curly braces) |
| Scope | `endpoint` (URL path), `body`, `query_params`, `headers` |
| Nesting | Resolved recursively through nested JSON objects and arrays |
| Unresolved | Placeholders with no matching key are left as-is |
| Order | Resolved after override merge, before auth injection |
| Logging | `api_logs` stores the **resolved** values |

### Supply Params at Call Time
```php
// PHP — direct service call
$apiCaller->call($apiConfig, $overrides, $params);
```
```json
// HTTP — via test endpoint POST /api/test-caller/execute
{
  "api_config_id": 1,
  "overrides": {},
  "params": {
    "user_id": 42,
    "status": "active"
  }
}
```

---

## 4. Summary of Parameters

### ApiConfig `auth_type` vs `auth_provider_id`
| Requirement | `auth_type` | `auth_provider_id` |
| :--- | :--- | :--- |
| **No Auth** | `none` | `null` |
| **Simple API Key (Inline)** | `api_key` | `null` |
| **Simple Basic Auth (Inline)** | `basic` | `null` |
| **OAuth2 / Bearer Token** | `external` | `{ID}` |
| **Centralized API Key** | `external` | `{ID}` |
| **Provider-Level Basic Auth** | `external` | `{ID}` |
| **HMAC / JWT (Provider)** | `external` | `{ID}` |

### AuthProvider `type` Capabilities
| `type` | Token Fetch | Caching | `isTokenBased()` | `isApiKey()` | Notes |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `none` | ✗ | ✗ | ✗ | ✗ | No-op |
| `api_key` | ✗ | ✗ | ✗ | ✓ | Key injected via `credentials.location` |
| `basic` | ✗ | ✗ | ✗ | ✗ | Schema supported; requires resolver extension |
| `bearer_token` | ✓ | ✓ | ✓ | ✗ | Static or fetched token |
| `oauth2` | ✓ | ✓ | ✓ | ✗ | Client-credentials / auth-code flow |
| `hmac` | ✗ | ✗ | ✗ | ✗ | Schema supported; requires resolver extension |
| `jwt` | ✗ | ✗ | ✗ | ✗ | Schema supported; requires resolver extension |

### `auth_input` Parsing Rules
- Split by newlines (`\n`, `\r\n`).
- Split keys and values by `:` or `=`.
- Trims whitespace automatically.
- For `basic` auth, it expects keys `username` and `password`.
- For `api_key`, it uses the exact keys and values provided.

### AuthProvider Token Caching Flags
| `token_cache_enabled` | `get_on_each_request` | Behaviour |
| :---: | :---: | :--- |
| `true` | `false` | Token cached; refreshed only when expired (recommended) |
| `true` | `true` | Fresh token fetched before every API call |
| `false` | `false` | No caching; token always re-fetched |

### `expiry_type` Values
| Value | Source field in response | Resolution |
| :--- | :--- | :--- |
| `seconds` | `response_mapping.expires_in` | `now() + expires_in seconds` |
| `timestamp` | `response_mapping.expires_at` | Parsed via `Carbon::parse()` |
| `null` | — | Token never considered expired by the system |

### `token_expiry_rules` Fields
| Field | Type | Description |
| :--- | :--- | :--- |
| `status_code` | integer | Triggers refresh when response HTTP status matches this value |
| `error_key` | string (dot-notation) | Key in response body to inspect |
| `error_value` | string | Expected value at `error_key` that indicates expiry |

### Dynamic `$params` — Supported Locations
| `ApiConfig` Field | Placeholder Support | Example |
| :--- | :---: | :--- |
| `endpoint` | ✓ | `/users/{{user_id}}/profile` |
| `body` | ✓ | `{"filter": "{{status}}"}` |
| `query_params` | ✓ | `{"page": "{{page}}"}` |
| `headers` | ✓ | `{"X-Tenant": "{{tenant}}"}` |
| `base_url` | ✗ | Fixed per config |
| `auth_config` / credentials | ✗ | Managed separately via Auth Provider |
