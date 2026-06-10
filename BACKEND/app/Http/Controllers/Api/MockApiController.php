<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MockApiController extends Controller
{
    /**
     * GET /api/mock/auth/config
     *
     * Public mock endpoint to simulate a third-party auth config discovery response.
     */
    public function authConfig()
    {
        return response()->json([
            'status' => 'success',
            'service' => 'Mock Third Party Auth',
            'auth_provider' => [
                'type' => 'oauth2',
                'token_url' => url('/api/mock/oauth/token'),
                'method' => 'POST',
                'credentials' => [
                    'client_id' => 'omni_app',
                    'client_secret' => 'omni_secret',
                    'grant_type' => 'client_credentials',
                ],
                'response_mapping' => [
                    'access_token' => 'access_token',
                    'refresh_token' => 'refresh_token',
                    'expires_in' => 'expires_in',
                ],
            ],
            'meta' => [
                'generated_at' => now()->toDateTimeString(),
                'trace_id' => uniqid('mock_auth_', true),
            ],
        ]);
    }

    /**
     * GET /api/mock/basic-auth
     */
    public function basicAuth(Request $request)
    {
        $user = $request->getUser();
        $pass = $request->getPassword();

        if ($user === 'admin' && $pass === 'password123') {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via Basic Auth',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Unauthorized Basic Auth'], 401);
    }

    /**
     * GET /api/mock/api-key
     */
    public function apiKey(Request $request)
    {
        $keyName = $request->query('key_name', 'X-API-KEY');
        $expectedKey = 'secret-token-789';

        $providedKey = $request->header($keyName) 
            ?? $request->query($keyName) 
            ?? $request->input($keyName);

        if ($providedKey === $expectedKey) {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via API Key',
                'location' => $request->header($keyName) ? 'header' : ($request->query($keyName) ? 'query' : 'body')
            ]);
        }

        return response()->json(['message' => 'Invalid API Key'], 401);
    }

    /**
     * POST /api/mock/oauth/token
     */
    public function oauthToken(Request $request)
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        // Simple validation
        if ($clientId === 'omni_app' && $clientSecret === 'omni_secret') {
            return response()->json([
                'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.HvWlHGRGr0VFjjCtQqEuCeAnxQPxo2rLp_AqZKddCjA',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'refresh_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsInJlZnJlc2giOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.wW0zpTpga2Y5ln_TLCb0Xrn_6uaroRaIv2RP70oaZPs'
            ]);
        }

        return response()->json(['error' => 'invalid_client'], 400);
    }

    /**
     * GET /api/mock/oauth/resource
     */
    public function oauthResource(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader === 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImlhdCI6MTUxNjIzOTAyMn0.HvWlHGRGr0VFjjCtQqEuCeAnxQPxo2rLp_AqZKddCjA') {
            return response()->json([
                'status' => 'success',
                'message' => 'Authenticated via OAuth2 Bearer Token',
                'data' => [
                    'leads' => [
                        ['id' => 1, 'name' => 'Lead A'],
                        ['id' => 2, 'name' => 'Lead B']
                    ]
                ]
            ]);
        }

        return response()->json(['error' => 'unauthorized'], 401);
    }

    /**
     * GET /api/mock/response/array-values
     */
    public function arrayValuesResponse()
    {
        return response()->json([
            'response_type' => 'array_values',
            'data' => ['Alpha', 'Beta', 'Gamma', 'Delta'],
            'extras' => [
                'request_id' => uniqid('arr_vals_', true),
                'source' => 'mock-service',
                'generated_at' => now()->toIso8601String(),
                'note' => 'Demo payload for dependency dropdown values',
            ],
        ]);
    }

    /**
     * GET /api/mock/response/array-objects
     */
    public function arrayObjectsResponse()
    {
        return response()->json([
            'response_type' => 'array_objects',
            'data' => [
                [
                    'label' => 'High Priority',
                    'value' => 'high',
                    'rank' => 1,
                    'active' => true,
                ],
                [
                    'label' => 'Medium Priority',
                    'value' => 'medium',
                    'rank' => 2,
                    'active' => true,
                ],
                [
                    'label' => 'Low Priority',
                    'value' => 'low',
                    'rank' => 3,
                    'active' => false,
                ],
            ],
            'extras' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 10,
                    'total' => 3,
                ],
                'environment' => 'mock',
            ],
        ]);
    }

    /**
     * GET /api/mock/response/single-value
     */
    public function singleValueResponse()
    {
        return response()->json([
            'response_type' => 'single_value',
            'data' => 'CONFIRMED',
            'extras' => [
                'status_code' => 200,
                'description' => 'Single value demo response',
                'random_score' => random_int(10, 99),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/mock/states
     *
     * Query params:
     * - country_id: 1 (India), 2 (USA), 3 (China), 4 (England)
     * - country_name: india, usa, china, england
     */
    public function states(Request $request)
    {
        generateLogs("info", "Received request for states with params: " . json_encode($request->all()));
        $statesByCountry = [
            1 => [
                'country_id' => 1,
                'country_name' => 'India',
                'states' => ['Maharashtra', 'Gujarat', 'Karnataka', 'Tamil Nadu'],
            ],
            2 => [
                'country_id' => 2,
                'country_name' => 'USA',
                'states' => ['California', 'Texas', 'Florida', 'New York'],
            ],
            3 => [
                'country_id' => 3,
                'country_name' => 'China',
                'states' => ['Guangdong', 'Zhejiang', 'Sichuan', 'Jiangsu'],
            ],
            4 => [
                'country_id' => 4,
                'country_name' => 'England',
                'states' => ['Greater London', 'Greater Manchester', 'Merseyside', 'West Midlands'],
            ],
        ];

        $countryId = $request->input('country_id');
        $countryName = $request->input('country_name');

        if ($countryId !== null && isset($statesByCountry[(int) $countryId])) {
            return response()->json($statesByCountry[(int) $countryId]);
        }

        if (!empty($countryName)) {
            $normalizedCountryName = strtolower(trim((string) $countryName));

            foreach ($statesByCountry as $countryData) {
                if (strtolower($countryData['country_name']) === $normalizedCountryName) {
                    return response()->json($countryData);
                }
            }
        }

        return response()->json([
            'message' => 'Country not found. Provide country_id (1-4) or country_name (india, usa, china, england).',
        ], 404);
    }

    /**
     * POST /api/mock/echo
     */
    public function echo(Request $request)
    {
        return response()->json([
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'query' => $request->query(),
            'body' => $request->all(),
        ]);
    }

    /**
     * POST /api/mock/simple/return-value
     *
     * Accepts payload key "Testing two" and mirrors it back as
     * "response_testing_two" for api_config based simple return value testing.
     */
    public function simpleReturnValue(Request $request)
    {
        return response()->json([
            'response_testing_two' => $request->input('Testing two'),
        ]);
    }

    /**
     * GET /api/mock/dependent/categories
     *
     * Parent field mock — returns array_values (list of category names).
     * Use this as the independent field whose selected value drives
     * both /dependent/sub-categories and /dependent/category-code.
     *
     * API Config wiring:
     *   - method:              GET
     *   - endpoint:            /api/mock/dependent/categories
     *   - auth_type:           none
     *   - response_type:       array_values
     *   - response_data_path:  data.categories
     */
    public function dependentCategories()
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'categories' => ['electronics', 'clothing', 'furniture'],
            ],
            'meta' => [
                'total'        => 3,
                'generated_at' => now()->toIso8601String(),
                'source'       => 'mock-dependent-categories',
            ],
        ]);
    }

    /**
     * POST /api/mock/dependent/sub-categories
     *
     * Dependent field mock — returns array_objects.
     * Driven by a shared parent field: "category".
     *
     * Request body:
     *   { "category": "electronics" }   // or: clothing, furniture
     *
     * API Config wiring:
     *   - method:              POST
     *   - endpoint:            /api/mock/dependent/sub-categories
     *   - auth_type:           none
     *   - response_type:       array_objects
     *   - response_data_path:  data.items
     *   - response_label_key:  label
     *   - response_value_key:  value
     *
     * Dependency mapping:
     *   - parent_field_name:   <your parent field name>
     *   - param_name:          category
     *   - param_type:          body
     */
    public function dependentSubCategories(Request $request)
    {
        $catalog = [
            'electronics' => [
                ['label' => 'Mobile Phones',   'value' => 'mobile_phones',   'sortable' => true],
                ['label' => 'Laptops',         'value' => 'laptops',         'sortable' => true],
                ['label' => 'Headphones',      'value' => 'headphones',      'sortable' => false],
                ['label' => 'Smart Watches',   'value' => 'smart_watches',   'sortable' => false],
            ],
            'clothing' => [
                ['label' => 'Men\'s Wear',     'value' => 'mens_wear',       'sortable' => true],
                ['label' => 'Women\'s Wear',   'value' => 'womens_wear',     'sortable' => true],
                ['label' => 'Kids\' Wear',     'value' => 'kids_wear',       'sortable' => false],
                ['label' => 'Sports Wear',     'value' => 'sports_wear',     'sortable' => true],
            ],
            'furniture' => [
                ['label' => 'Living Room',     'value' => 'living_room',     'sortable' => true],
                ['label' => 'Bedroom',         'value' => 'bedroom',         'sortable' => true],
                ['label' => 'Office',          'value' => 'office',          'sortable' => false],
                ['label' => 'Outdoor',         'value' => 'outdoor',         'sortable' => false],
            ],
        ];

        $category = strtolower(trim((string) $request->input('category', '')));

        if ($category === '' || !array_key_exists($category, $catalog)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unknown or missing category. Valid values: electronics, clothing, furniture.',
                'data'    => ['items' => []],
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'items' => $catalog[$category],
            ],
            'meta' => [
                'category'     => $category,
                'total'        => count($catalog[$category]),
                'generated_at' => now()->toIso8601String(),
                'source'       => 'mock-dependent-sub-categories',
            ],
        ]);
    }

    /**
     * POST /api/mock/dependent/category-code
     *
     * Dependent field mock — returns single_value.
     * Driven by the same parent field: "category".
     *
     * Request body:
     *   { "category": "electronics" }   // or: clothing, furniture
     *
     * API Config wiring:
     *   - method:              POST
     *   - endpoint:            /api/mock/dependent/category-code
     *   - auth_type:           none
     *   - response_type:       single_value
     *   - response_data_path:  data.code
     *
     * Dependency mapping:
     *   - parent_field_name:   <your parent field name>   (same parent as above)
     *   - param_name:          category
     *   - param_type:          body
     */
    public function dependentCategoryCode(Request $request)
    {
        generateLogs("info", "Received request for category-code with params: " . json_encode($request->all()));

        $codes = [
            'electronics' => 'CAT-ELEC-001',
            'clothing'    => 'CAT-CLTH-002',
            'furniture'   => 'CAT-FURN-003',
        ];

        $category = strtolower(trim((string) $request->input('category', '')));

        if ($category === '' || !array_key_exists($category, $codes)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unknown or missing category. Valid values: electronics, clothing, furniture.',
                'data'    => ['code' => null],
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'code' => $codes[$category],
            ],
            'meta' => [
                'category'     => $category,
                'generated_at' => now()->toIso8601String(),
                'source'       => 'mock-dependent-category-code',
            ],
        ]);
    }
}
