<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiConfigRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\ApiConfig;
use Exception;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class ApiConfigController extends Controller
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function store(ApiConfigRequest $request)
    {
        try {
            $data = $request->validated();

            // Parse inline auth input (your new UX feature)
            if (!empty($data['auth_input']) && $data['auth_type'] !== 'none') {
                $data['auth_config'] = [
                    'location' => $data['auth_location'],
                    'params' => $this->parseKeyValueInput($data['auth_input'])
                ];
            }

            unset($data['auth_input'], $data['auth_location']);

            $api = ApiConfig::create($data);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $api, trans('message.api_config_created'), ResponseHTTP::HTTP_CREATED);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    private function parseKeyValueInput(string $input): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($input));
        $result = [];

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
            } elseif (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
            } else {
                continue;
            }

            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    public function update(ApiConfigRequest $request, ApiConfig $apiConfig)
    {
        try {
            $data = $request->validated();

            // Handle inline auth parsing
            if (!empty($data['auth_input']) && $data['auth_type'] !== 'none') {
                $data['auth_config'] = [
                    'location' => $data['auth_location'],
                    'params' => $this->parseKeyValueInput($data['auth_input']),
                ];
            }

            unset($data['auth_input'], $data['auth_location']);

            // If switching to auth_provider → clear inline auth
            if (!empty($data['auth_provider_id'])) {
                $data['auth_type'] = 'none';
                $data['auth_config'] = null;
            }

            // If switching to inline auth → remove provider
            if (!empty($data['auth_type']) && $data['auth_type'] !== 'none') {
                $data['auth_provider_id'] = null;
            }

            $apiConfig->update($data);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $apiConfig->fresh(), trans('message.api_config_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function index()
    {
        try {
            $apis = ApiConfig::with('authProvider')
                ->latest()
                ->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $apis, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching API Configs: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function show(ApiConfig $apiConfig)
    {
        try {
            return $this->apiResponse->getResponseStructure(trans('message.success'), $apiConfig->load('authProvider'), trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function destroy(ApiConfig $apiConfig)
    {
        try {
            if ($apiConfig->status) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.api_config_active'), ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            }

            $apiConfig->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), null, trans('message.api_config_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
