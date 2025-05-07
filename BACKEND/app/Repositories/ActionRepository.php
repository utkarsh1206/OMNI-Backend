<?php

namespace App\Repositories;

use App\Models\Action;
use App\Http\Response\CustomApiResponse;
use Exception;

class ActionRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $actions = Action::all();

            return $this->apiResponse->getResponseStructure(
                trans('message.success'),
                $actions,
                trans('message.data_fetch')
            );
        } catch (Exception $e) {
            generateLogs('error', 'Error in Action List: ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
