# Feature Documentation: Centralized API Management & Authentication System

## 1. Introduction
The **Centralized API Management & Authentication System** is a robust framework designed to streamline how the OMNI Backend interacts with external third-party services. Instead of hardcoding API logic and credentials across the codebase, this system allows administrators to configure, manage, and monitor all external integrations from a single interface.

---

## 2. Core Capabilities
- **Dynamic Configuration**: Configure base URLs, endpoints, and HTTP methods without code changes.
- **Runtime Dynamic Parameters**: Embed `{{placeholder}}` tokens in any stored field (URL path, body, query params, headers) and supply values at call time — no stored config change required.
- **Advanced Authentication**: Supports everything from simple API keys to complex OAuth2 flows with automatic token refreshing.
- **Smart Token Lifecycle**: Automatically caches tokens, checks for expiry, and handles background refreshes to prevent request failures.
- **Audit & Logging**: Every request and response is logged, providing a full audit trail for debugging and compliance.
- **Resiliency**: Built-in support for request timeouts and automatic retries.

---

## 3. System Architecture

### 3.1 Data Models
1.  **Auth Provider (`AuthProvider`)**: Stores "how" to authenticate. It contains the logic for obtaining tokens or applying credentials.
2.  **API Config (`ApiConfig`)**: Stores "what" to call. It defines the specific endpoint and links to either a specific Auth Provider or uses its own "Inline" credentials.
3.  **API Token (`ApiToken`)**: A storage layer for active access and refresh tokens linked to an Auth Provider.
4.  **API Log (`ApiLog`)**: A ledger of every execution attempt, including payloads, response status, and error messages.

### 3.2 Service Layer
- **`ApiCallerService`**: The high-level orchestrator used by the rest of the application to execute a call.
- **`AuthResolverService`**: Logic to inject the correct credentials into the HTTP request (Header, Query, or Body).
- **`TokenManagerService`**: Logic to communicate with external identity providers to fetch/refresh tokens.
- **`HttpClientService`**: Standardized wrapper for making HTTP requests with consistent error handling.

---

## 4. Workflows

### 4.1 Setup Workflow
1.  **(Optional) Create Auth Provider**: If the external service requires OAuth2 or is used by multiple endpoints, create an Auth Provider first.
2.  **Create API Config**: Define the endpoint (e.g., `/v1/get-data`).
3.  **Choose Auth Strategy**:
    - **None**: For public APIs.
    - **Inline**: For simple `api_key` or `basic` auth unique to this endpoint.
    - **Provider**: Link to the Auth Provider created in Step 1.

### 4.2 Execution Workflow (Internal)
When `ApiCallerService::call()` is triggered:
1.  **Build Request**: Merges default headers/query/body with any runtime overrides.
2.  **Resolve Placeholders**: If `$params` are provided, all `{{placeholder}}` tokens in headers, query params, body, and the URL path are replaced with the supplied runtime values.
3.  **Resolve Auth**:
    - If **Inline**: Injects credentials directly.
    - If **Provider**:
        - Checks `api_tokens` for a valid cached token.
        - If expired or missing, calls `TokenManagerService` to fetch a new one.
        - Injects the token (usually as `Bearer {token}`).
4.  **Execute Call**: Sends the request via `HttpClientService`.
5.  **Expiry Check**: If the response indicates a token expiry (based on pre-configured rules), it refreshes the token and **retries the call once automatically**.
6.  **Log Result**: Saves the fully resolved request/response cycle to `api_logs`.

### 4.3 Dynamic Parameter Resolution
All four parts of a stored `ApiConfig` support `{{placeholder}}` syntax:

| Location | Example stored value | Resolved with `params: {"id": 42}` |
|---|---|---|
| `endpoint` | `/users/{{id}}/profile` | `/users/42/profile` |
| `body` | `{"user_id": "{{id}}"}` | `{"user_id": "42"}` |
| `query_params` | `{"filter": "{{status}}"}` | `{"filter": "active"}` |
| `headers` | `{"X-Tenant": "{{tenant}}"}` | `{"X-Tenant": "acme"}` |

- Placeholder resolution happens **after** override merging but **before** auth injection.
- Unresolved placeholders (no matching key in `$params`) are left unchanged.
- Resolution is recursive — nested JSON structures are fully traversed.

---

## 5. Detailed Field Descriptions

### 5.1 API Configuration (`api_configs`)

| Field | Type | Required? | Description |
|---|---|---|---|
| `name` | String | Yes | Unique name for the integration. |
| `base_url` | String | Yes | The root URL (e.g., `https://api.crm.com`). |
| `endpoint` | String | Yes | The specific path (e.g., `/users/create`). |
| `method` | Enum | Yes | `GET`, `POST`, `PUT`, `DELETE`. |
| `auth_type` | Enum | Yes | `none`, `api_key`, `basic`, `external`. Use `external` if using an Auth Provider. |
| `auth_provider_id` | ID | No | Reference to an `auth_providers` record. |
| `auth_input` | String | Case | (UI Only) Textarea to input credentials in `Key: Value` format. |
| `auth_location` | Enum | Case | Where to put inline credentials: `header`, `query`, or `body`. |
| `timeout` | Integer | No | Max seconds to wait (Default: 30). |
| `retry_count` | Integer | No | Number of times to retry on network failure. |

### 5.2 Auth Provider (`auth_providers`)

| Field | Type | Case | Description |
|---|---|---|---|
| `type` | Enum | Yes | `api_key`, `basic`, `bearer_token`, `oauth2`, `hmac`, `jwt`. |
| `token_url` | String | Token-based | URL used to fetch a new access token. |
| `credentials` | JSON | API Key | Stores `key_name`, `api_key`, and `location`. |
| `request_config`| JSON | Token-based | The body/headers sent to the `token_url` (e.g., client secret). |
| `response_mapping`| JSON | Token-based | Map where the token is in the JSON response (e.g., `data.access_token`). |
| `expiry_type` | Enum | Optional | How to calculate expiry: `seconds` or `timestamp`. |
| `token_expiry_rules`| JSON | Optional | Rules to detect expiry from a main API response (e.g., Status Code 401). |

---

## 6. Case-Specific Logic

### Case A: Simple API Key (Inline)
- Set `auth_type` to `api_key`.
- Set `auth_location` to `header`.
- Set `auth_input` to `X-API-KEY: your_secret_here`.
- **Result**: Every call will include `X-API-KEY` in the headers.

### Case B: OAuth2 Client Credentials
- Create an **Auth Provider**.
- Set `type` to `oauth2`.
- `token_url`: `https://identity.server.com/token`.
- `request_config`: Include `client_id` and `client_secret` in the `body`.
- `response_mapping`: `access_token` => `access_token`.
- Create **API Config** with `auth_type = external` and link to this provider.
- **Result**: The system handles token storage and automatic refresh.

### Case C: Dynamic Runtime Parameters
- Store an `ApiConfig` with `endpoint` = `/orders/{{order_id}}/items` and `body` = `{"status": "{{status}}"}`.
- At call time, pass `$params = ['order_id' => 99, 'status' => 'pending']`.
- **Result**: The outgoing request hits `/orders/99/items` with body `{"status": "pending"}` — no stored config changes needed.

## 7. Technical Constraints & Business Rules

### 7.1 Deletion Safeguards
- **API Configs**: An API configuration cannot be deleted if its `status` is set to `true` (Active). This prevents accidental breakage of live integrations. You must disable the API first.
- **Auth Providers**: An Auth Provider cannot be deleted if it is currently linked to one or more API Configurations. This ensures referential integrity and prevents "orphaned" APIs.

### 7.2 Token Invalidation
To ensure security and consistency, the system automatically deletes any cached tokens (`ApiToken`) when the following fields in an **Auth Provider** are updated:
- `credentials`
- `request_config`
- `response_mapping`
- `token_url`
- `method`
This forces the system to fetch a fresh token using the new configuration on the next API call.

### 7.3 Validation Rules (Request Level)
The system enforces strict validation via Laravel Form Requests:
- **Mode Matching**: `auth_provider_id` is only valid when `auth_type = external`.
- **Automatic Cleanup**: 
  - If `auth_type = none`, the system clears both provider and inline auth config.
  - If you switch an API to an inline auth mode (`api_key` / `basic`), the system clears the `auth_provider_id`.
  - If you switch an API to `external`, the system clears inline auth config.
- **Required Fields**: For Inline auth, `auth_input` and `auth_location` become required during updates if not already present.

---

## 8. Service Responsibilities (Deep Dive)

### 8.1 `ApiCallerService`
- Primary entry point for code-based API calls. Signature: `call(ApiConfig $apiConfig, array $override = [], array $params = [])`.
- Merges runtime overrides with stored configurations.
- Resolves all `{{placeholder}}` tokens in headers, query params, body, and the URL using the supplied `$params` array.
- Handles the "Retry on Expiry" logic: if a call fails and the Auth Provider confirms it's an expiry issue, it refreshes the token and retries exactly once.
- Logs every attempt, ensuring `success` (boolean) and `status_code` are always captured.

### 8.2 `AuthResolverService`
- The "Injection Engine".
- Knows how to format a `Bearer` token into an `Authorization` header.
- Knows how to place API keys into headers, query params, or body payloads based on configuration.
- Handles the `Basic` auth encoding (Base64).

### 8.3 `TokenManagerService`
- The "Identity Client".
- Manages the `api_tokens` table.
- Uses `HttpClientService` to talk to the `token_url`.
- Uses the `response_mapping` defined in the Auth Provider to find the token in complex JSON responses.

### 8.4 `HttpClientService`
- A wrapper around Laravel's `Http` facade.
- Standardizes the response format into an array: `['status', 'body', 'error']`.
- Centralizes timeout and retry configuration at the transport level.

---

## 9. Example Scenarios & Payloads

### Scenario 1: Setting up an Inline API Key (POST `/api/api-configs`)
**Payload:**
```json
{
  "name": "Weather API",
  "base_url": "https://api.weather.com",
  "endpoint": "/current",
  "method": "GET",
  "auth_type": "api_key",
  "auth_location": "query",
  "auth_input": "appid: 12345abcde"
}
```
**Effect**: Every request to this API will append `?appid=12345abcde` to the URL.

### Scenario 3: API Call with Dynamic Runtime Parameters
**Stored `ApiConfig`:**
```json
{
  "name": "Get Order Items",
  "base_url": "https://api.example.com",
  "endpoint": "/v1/orders/{{order_id}}/items",
  "method": "POST",
  "auth_type": "external",
  "auth_provider_id": 1,
  "body": {
    "status": "{{status}}",
    "tenant": "{{tenant}}"
  },
  "headers": {
    "X-Tenant-ID": "{{tenant}}"
  }
}
```
**Code call:**
```php
$response = $apiCaller->call($apiConfig, [], [
    'order_id' => 99,
    'status'   => 'pending',
    'tenant'   => 'acme',
]);
```
**Effect**: Outgoing URL is `https://api.example.com/v1/orders/99/items`, body is `{"status": "pending", "tenant": "acme"}`, and header `X-Tenant-ID: acme` is injected — all without modifying the stored config.

---

### Scenario 2: Setting up OAuth2 (POST `/api/auth-providers`)
**Payload:**
```json
{
  "name": "Salesforce Auth",
  "type": "oauth2",
  "token_url": "https://login.salesforce.com/services/oauth2/token",
  "method": "POST",
  "request_config": {
    "body": {
      "grant_type": "client_credentials",
      "client_id": "...",
      "client_secret": "..."
    }
  },
  "response_mapping": {
    "access_token": "access_token",
    "expires_in": "expires_in"
  },
  "expiry_type": "seconds"
}
```

---

## 10. Operational Notes

### Security
- Auth credentials (passwords, secrets) are stored in the database. Ensure the database is encrypted and access to these tables is restricted.
- Tokens are cached to reduce latency and avoid hitting rate limits on identity providers.

### Error Handling
- If an API call fails after all retries, the `ApiLog` will contain the `error_message` returned by the server.
- Validation prevents linking an API to both an Inline auth and a Provider auth simultaneously.

### UI Feature: Inline Parsing
The system supports a user-friendly input for credentials. Instead of crafting complex JSON, users can type:
```text
username: admin
password: secret_password
```
The backend automatically parses this into the structured `auth_config` JSON.
