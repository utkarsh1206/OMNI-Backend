<?php

namespace App\Repositories;

use App\Models\TenantModule;
use App\Http\Response\CustomApiResponse;
use Exception;

class TenantModuleRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $modules = TenantModule::all();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $modules,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Tenant Module List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
