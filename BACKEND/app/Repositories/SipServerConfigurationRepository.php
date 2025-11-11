<?php

namespace App\Repositories;

use App\Models\SipServerConfiguration;
use App\Http\Resources\NeoxConfigurationResource;
use Illuminate\Http\Request;
use Exception;
use App\Http\Response\CustomApiResponse;

class SipServerConfigurationRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    /**
     * Retrieve and list all SIP server configurations with optional filtering and pagination.
     */
    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $query = SipServerConfiguration::query();

            // Search Filter: Search by sip_server_url or sip_ua_uri_domain
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('sip_server_url', 'like', "%$searchTerm%")
                        ->orWhere('sip_ua_uri_domain', 'like', "%$searchTerm%");
                });
            }

            $listData = $page == config('omni.ALL_PAGE') ? $query->get() : $query->paginate($perPage);

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $listData,
                trans('message.data_fetch')
            );
        } catch (\Exception $e) {
            // Placeholder for custom logging function
            generateLogs('error', 'Error in listing SIP Server Configurations: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    /**
     * Create a new SIP server configuration record.
     */
    public function create(Request $request)
    {
        try {
            // Check for uniqueness of sip_server_url
            if (SipServerConfiguration::where('sip_server_url', $request->sip_server_url)->exists()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.sip_server_url_exists')
                );
            }

            // Check for uniqueness of sip_ua_uri_domain
            if (SipServerConfiguration::where('sip_ua_uri_domain', $request->sip_ua_uri_domain)->exists()) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.sip_ua_uri_domain_exists')
                );
            }

            $configuration = SipServerConfiguration::create([
                'sip_server_url' => $request->sip_server_url,
                'sip_ua_uri_domain' => $request->sip_ua_uri_domain,
            ]);

            $data = new NeoxConfigurationResource($configuration);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.configuration_create_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in creating SIP Server Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Display a single SIP server configuration.
     */
    public function show(SipServerConfiguration $sipServerConfiguration)
    {
        try {
            $data = new NeoxConfigurationResource($sipServerConfiguration);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.data_fetch')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in showing SIP Server Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Update an existing SIP server configuration record.
     */
    public function update(Request $request, SipServerConfiguration $sipServerConfiguration)
    {
        try {
            $id = $sipServerConfiguration->id;

            // Check for uniqueness of sip_server_url, excluding the current record
            if (SipServerConfiguration::where('sip_server_url', $request->sip_server_url)
                ->where('id', '!=', $id)->exists()
            ) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.sip_server_url_exists')
                );
            }

            // Check for uniqueness of sip_ua_uri_domain, excluding the current record
            if (SipServerConfiguration::where('sip_ua_uri_domain', $request->sip_ua_uri_domain)
                ->where('id', '!=', $id)->exists()
            ) {
                return $this->apiResponse->getResponseStructure(
                    trans('message.error'),
                    null,
                    trans('message.sip_ua_uri_domain_exists')
                );
            }

            $sipServerConfiguration->update([
                'sip_server_url' => $request->sip_server_url ?? $sipServerConfiguration->sip_server_url,
                'sip_ua_uri_domain' => $request->sip_ua_uri_domain ?? $sipServerConfiguration->sip_ua_uri_domain,
            ]);

            $data = new NeoxConfigurationResource($sipServerConfiguration);
            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $data,
                trans('message.configuration_update_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in updating SIP Server Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    /**
     * Delete a SIP server configuration record.
     */
    public function delete(SipServerConfiguration $sipServerConfiguration)
    {
        try {
            $id = $sipServerConfiguration->id;
            $sipServerConfiguration->delete();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $id,
                trans('message.configuration_delete_success')
            );
        } catch (Exception $ex) {
            generateLogs('error', 'Error in deleting SIP Server Configuration: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
