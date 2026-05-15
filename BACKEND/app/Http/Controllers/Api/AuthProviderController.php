<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthProviderRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\AuthProvider;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class AuthProviderController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store(AuthProviderRequest $request)
    {
        try {
            $data = $request->validated();
            $provider = AuthProvider::create($data);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $provider, trans('message.auth_provider_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating Auth Provider: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function update(AuthProviderRequest $request, AuthProvider $authProvider)
    {
        try {
            $data = $request->validated();
            $authProvider->update($data);
            // CRITICAL: Invalidate token if auth config changed
            if ($this->shouldInvalidateToken($authProvider, $data)) {
                $authProvider->token()?->delete();
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $authProvider->fresh(), trans('message.auth_provider_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating Auth Provider: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
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
        try {
            $providers = AuthProvider::with('token')
                ->latest()
                ->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $providers, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Auth Providers: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show(AuthProvider $authProvider)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $authProvider->load('token'), trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Auth Provider: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function destroy(AuthProvider $authProvider)
    {
        try {
            // Prevent delete if used by any API
            if ($authProvider->apiConfigs()->exists()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.auth_provider_in_use'), ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Delete token (cleanup)
            $authProvider->token()?->delete();
            $authProvider->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), null, trans('message.auth_provider_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting Auth Provider: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
