<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthProviderRequest;
use App\Models\AuthProvider;
use Illuminate\Support\Facades\DB;

class AuthProviderController extends Controller
{
    public function store(AuthProviderRequest $request)
    {
        $data = $request->validated();

        $provider = AuthProvider::create($data);

        return response()->json($provider);
    }

    public function update(AuthProviderRequest $request, AuthProvider $authProvider)
    {
        $data = $request->validated();
        $authProvider->update($data);
        // CRITICAL: Invalidate token if auth config changed
        if ($this->shouldInvalidateToken($authProvider, $data)) {
            $authProvider->token()?->delete();
        }
        return response()->json($authProvider->fresh());
    }

    private function shouldInvalidateToken(AuthProvider $provider, array $newData): bool
    {
        $dirtyFields = [
            'credentials',
            'request_config',
            'response_mapping',
            'token_url',
            'method'
        ];

        foreach ($dirtyFields as $field) {
            if (array_key_exists($field, $newData)) {
                return true;
            }
        }

        return false;
    }

    public function index()
    {
        $providers = AuthProvider::with('token')
            ->latest()
            ->get();

        return response()->json($providers);
    }

    public function show(AuthProvider $authProvider)
    {
        return response()->json(
            $authProvider->load('token')
        );
    }

    public function destroy(AuthProvider $authProvider)
    {
        // ❗ Prevent delete if used by any API
        if ($authProvider->apiConfigs()->exists()) {
            return response()->json([
                'message' => 'Cannot delete. This auth provider is used by APIs.'
            ], 422);
        }

        // Delete token (cleanup)
        $authProvider->token()?->delete();

        $authProvider->delete();

        return response()->json([
            'message' => 'Auth Provider deleted successfully'
        ]);
    }
}
