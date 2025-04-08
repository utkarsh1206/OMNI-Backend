<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;

class UpdateCustomFieldRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function index(Request $request)
    {
        // Retrieve inputs
        $custom_query = $request->input('custom_query');
        $list_id = $request->input('list_id');
        $closecallid = $request->input('uniqueid');

        if (!is_array($custom_query)) {
            return response()->json([
                'message' => trans("message.custom_query_error")
            ], ResponseHTTP::HTTP_BAD_REQUEST);
        }

        // Check if there is an existing record
        $count = DB::table("custom_cdr_{$list_id}")
            ->where('uniqueid', $closecallid)
            ->count();

        if ($count > 0) {
            // Update the record if it exists
            $affectedRows = DB::table("custom_cdr_{$list_id}")
                ->where('uniqueid', $closecallid)
                ->update($custom_query);

            return response()->json([
                'message' => trans("message.record_modified"),
                'affected_rows' => $affectedRows
            ]);
        } else {
            // Ensure custom_query is not empty before inserting
            if (empty($custom_query)) {
                return response()->json([
                    'message' => trans("message.no_data_insert"),
                ], ResponseHTTP::HTTP_BAD_REQUEST);
            }

            // Insert the record if it doesn't exist
            try {
                $insertedId = DB::table("custom_cdr_{$list_id}")
                    ->insertGetId($custom_query);

                return response()->json([
                    'message' =>  trans("message.record_created"),
                    'inserted_id' => $insertedId
                ]);
            } catch (\Exception $e) {
                generateLogs('error', 'Error during User Group List : ', $e->getMessage());
                return $this->apiResponse->handleAndResponseException($e);
            }
        }
    }
}
