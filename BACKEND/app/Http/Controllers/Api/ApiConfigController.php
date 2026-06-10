<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiConfigRequest;
use App\Http\Response\CustomApiResponse;
use App\Models\ApiConfig;
use Exception;
use Illuminate\Http\Request;
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
            $authType = $data['auth_type'] ?? 'none';

            // Parse inline auth only for inline auth modes.
            if (in_array($authType, ['api_key', 'basic'], true) && !empty($data['auth_input'])) {
                $data['auth_config'] = [
                    'location' => $data['auth_location'],
                    'params' => $this->parseKeyValueInput($data['auth_input'])
                ];
            }

            if ($authType === 'external') {
                $data['auth_config'] = null;
            }

            if ($authType === 'none') {
                $data['auth_provider_id'] = null;
                $data['auth_config'] = null;
            }

            if (in_array($authType, ['api_key', 'basic'], true)) {
                $data['auth_provider_id'] = null;
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
            $authType = $data['auth_type'] ?? $apiConfig->auth_type;

            // Parse inline auth only for inline auth modes.
            if (in_array($authType, ['api_key', 'basic'], true) && !empty($data['auth_input'])) {
                $data['auth_config'] = [
                    'location' => $data['auth_location'],
                    'params' => $this->parseKeyValueInput($data['auth_input']),
                ];
            }

            if ($authType === 'external') {
                $data['auth_config'] = null;
            }

            if ($authType === 'none') {
                $data['auth_provider_id'] = null;
                $data['auth_config'] = null;
            }

            if (in_array($authType, ['api_key', 'basic'], true)) {
                $data['auth_provider_id'] = null;
            }

            unset($data['auth_input'], $data['auth_location']);

            $apiConfig->update($data);

            return $this->apiResponse->getResponseStructure(trans('message.success'), $apiConfig->fresh(), trans('message.api_config_updated'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function index(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');

            $filters = [
                'name'      => $request->input('name'),
                'type'      => $request->input('type'),
                'method'    => $request->input('method'),
                'status'    => $request->input('status'),
                'auth_type' => $request->input('auth_type'),
            ];

            $query = ApiConfig::with('authProvider')->latest();

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('base_url', 'like', "%{$searchTerm}%")
                        ->orWhere('endpoint', 'like', "%{$searchTerm}%");
                });
            }

            if (!empty($filters['name'])) {
                $query->where('name', 'like', "%{$filters['name']}%");
            }

            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (!empty($filters['method'])) {
                $query->where('method', $filters['method']);
            }

            if (!empty($filters['auth_type'])) {
                $query->where('auth_type', $filters['auth_type']);
            }

            if (!is_null($filters['status']) && $filters['status'] !== '') {
                $status = filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if (!is_null($status)) {
                    $query->where('status', $status);
                }
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page    = $request->input('page', config('omni.DEFAULT_PAGE'));

            $apis = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

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
            // if ($apiConfig->status) {
            //     return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.api_config_active'), ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY);
            // }

            $apiConfig->delete();

            return $this->apiResponse->getResponseStructure(trans('message.success'), null, trans('message.api_config_deleted'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting API Config: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
