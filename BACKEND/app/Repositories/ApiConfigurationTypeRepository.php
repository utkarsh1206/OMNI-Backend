<?php

namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use App\Models\ApiConfigurationType;
use Exception;

class ApiConfigurationTypeRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $providers = ApiConfigurationType::get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $providers, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Api Configuratuon Type List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

}
