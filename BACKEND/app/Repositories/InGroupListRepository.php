<?php

namespace App\Repositories;

use App\Models\EcdialInboundGroup;
use App\Http\Response\CustomApiResponse;
use Illuminate\Database\QueryException;

class InGroupListRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $query = EcdialInboundGroup::query();
            $groups = $query->orderBy('group_id')->get(['group_id', 'group_name']);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $groups, trans('message.data_fetch'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error in generating failed Ingroup List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
