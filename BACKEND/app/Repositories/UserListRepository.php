<?php

namespace App\Repositories;

use App\Models\EcdialUser;
use App\Http\Response\CustomApiResponse;
use Illuminate\Database\QueryException;

class UserListRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        try {
            $query = EcdialUser::query();
            $users = $query->where('user_level', '<', 8)
                ->orderBy('user')
                ->get(['user']);
            return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.data_fetch'));
        } catch (QueryException $e) {
            generateLogs('error', 'Error during User List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
