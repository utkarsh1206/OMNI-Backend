<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronCommand;
use App\Http\Response\CustomApiResponse;

class CronCommandRepository
{


    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        $searchTerm = $request->input('search', '');

        $query = CronCommand::select("id", "command_signature", "type")->where('type', 'cron');

        // If there's a search term, apply it to the query
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('command_signature', 'like', '%' . $searchTerm . '%');
            });
        }

        // Paginate the results
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if ($page === 'all') {
            // If 'page' is 'all', retrieve all records without pagination
            $commands = $query->get();
            return $this->apiResponse->getResponseStructure(trans('message.success'), $commands, trans('message.data_fetch'));
        } else {
            // Paginate the results
            $commands = $query->paginate($perPage);
            // Return the response with pagination meta
            return $this->apiResponse->getResponseStructure(trans('message.success'), $commands, trans('message.data_fetch'));
        }
    }
}
