<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CronCommand;
use App\Http\Response\CustomApiResponse;
use Exception;

class CronCommandRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function list(Request $request)
    {
        try {
            $searchTerm = $request->input('search', '');

            $query = CronCommand::select("id", "command_signature", "type")->where('type', 'cron');

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('command_signature', 'like', '%' . $searchTerm . '%');
                });
            }

            // Paginate the results
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            if ($page === config('omni.ALL_PAGE')) {
                $commands = $query->get();
            } else {
                // Paginate the results
                $commands = $query->paginate($perPage);
            }
            return $this->apiResponse->getResponseStructure(trans('message.success'), $commands, trans('message.data_fetch'));
        } catch (Exception $ex) {
            generateLogs('error', 'Error in fetching Cron command listing: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
