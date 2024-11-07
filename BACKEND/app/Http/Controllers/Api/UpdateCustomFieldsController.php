<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCustomFieldsController extends Controller
{
    public function updateInsertCustomField(Request $request)
    {
        // Retrieve inputs
        $custom_query = $request->input('custom_query');
        $list_id = $request->input('list_id');
        $closecallid = $request->input('uniqueid');
        $domain = $request->input('domain');

        // Validate that custom_query is an array
        if (!is_array($custom_query)) {
            return response()->json([
                'message' => 'The custom_query should be an array.'
            ], 400);
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
                'message' => 'Record has been modified successfully.',
                'affected_rows' => $affectedRows
            ]);
        } else {
            // Ensure custom_query is not empty before inserting
            if (empty($custom_query)) {
                return response()->json([
                    'message' => 'No data provided to insert.'
                ], 400);
            }

            // Insert the record if it doesn't exist
            try {
                $insertedId = DB::table("custom_cdr_{$list_id}")
                    ->insertGetId($custom_query);
                
                return response()->json([
                    'message' => 'Record has been inserted successfully.',
                    'inserted_id' => $insertedId
                ]);
            } catch (\Exception $e) {
                // Log any exception that occurs during insertion
                Log::error('Insert failed: ' . $e->getMessage());
                
                return response()->json([
                    'message' => 'Error inserting the record.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }
}
