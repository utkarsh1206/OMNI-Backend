<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CronCommand;



class CronCommandsController extends Controller
{
    public function index(Request $request)
    {
            // Get the search term from the request
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
                return response()->json([
                    'data' => $commands,
                ]);
            } else {
                // Paginate the results
                $commands = $query->paginate($perPage);
                // Return the response with pagination meta
                return response()->json([
                    'data' => $commands->items(),
                    'meta' => [
                        'current_page' => $commands->currentPage(),
                        'last_page' => $commands->lastPage(),
                        'per_page' => $commands->perPage(),
                        'total' => $commands->total(),
                    ],
                ]);
            }
    }
}
