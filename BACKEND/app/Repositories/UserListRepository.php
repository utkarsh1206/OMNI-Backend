<?php

namespace App\Repositories;
use App\Models\EcdialUser;
use App\Http\Response\CustomApiResponse;


class UserListRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list()
    {
        $query = EcdialUser::query();
        $users = $query->where('user_level', '<', 8)
                       ->orderBy('user')
                       ->get(['user']);
        return $this->apiResponse->getResponseStructure(trans('message.success'), $users, trans('message.data_fetch'));
    }

   

}
