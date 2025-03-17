<?php

namespace App\Repositories;

use App\Models\EcdialListField;
use App\Models\LeadImportHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Facades\Log;
use App\Http\Response\CustomApiResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class ImportExportLeadRepository
{
    private $apiResponse;
    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function getFieldsByListId($list_id)
    {
        try {
            return EcdialListField::where('list_id', $list_id)
                ->orderBy('field_order')
                ->get();
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function validateCsvHeaders(array $csvHeaders, array $expectedHeaders)
    {
        return $csvHeaders === $expectedHeaders;
    }

    public function prepareDataForInsertion(array $csvData, array $expectedHeaders)
    {
        $validData = [];

        foreach ($csvData as $index => $row) {
            $rowData = array_combine($expectedHeaders, array_map('trim', $row));

            $validData[] = $rowData;
        }

        return $validData;
    }

    public function insertDataIntoTable($list_id, array $validData, $fileName, $fileType)
    {
        $tableName = "custom_lead_" . $list_id;

        if (!Schema::hasTable($tableName)) {
            return $this->apiResponse->getResponseStructure(trans('message.error'), $tableName, trans('message.table_not_exist'));
        }

        $fields = $this->getFieldsByListId($list_id);
        $insertedCount = 0; // Track the number of successfully inserted rows
        $failedCount = 0; // Track failed insertions
        $importedData = [];

        foreach ($validData as $row) {
            $insertData = [];

            foreach ($fields as $field) {
                $insertData[$field->field_name] = $row[$field->field_label] ?? null;
            }

            $insertData['created_at'] = now();
            $insertData['updated_at'] = now();

            try {
                DB::table($tableName)->insert($insertData);
                $insertedCount++; // Increment count on successful insertion
                $status = 'success';
            } catch (\Exception $e) {
                $failedCount++; // Increment failed count on error
                $status = 'failed';
                return $this->apiResponse->handleAndResponseException($e);
            }

            $importedData[] = [
                'row' => $row,
                'status' => $status
            ];
        }

        $data = [
            'imported_data' => $importedData, // Include the detailed import status data
            'inserted_count' => $insertedCount,
            'failed_count' => $failedCount
        ];

        // Log the import history
        $this->logImportHistory($list_id, $insertedCount, $failedCount, $fileName, $fileType);

        // After the loop, return success if any rows were inserted
        if ($insertedCount > 0) {
            // Make sure you're sending a message that indicates success.
            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_inserted_successfully'));
        } else {
            return $this->apiResponse->getResponseStructure(trans('message.error'), $data, trans('message.no_data_inserted'));
        }
    }

    public function downloadSampleCsv($list_id)
    {
        try {
            $fields = $this->getFieldsByListId($list_id);

            if ($fields->isEmpty()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $list_id, trans('message.no_fields_found_for_list'));
            }

            $headers = $fields->pluck('field_label')->toArray();

            return $this->createCsvResponse($headers, $list_id);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    public function processCsvFile($file, $list_id)
    {
        try {
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getClientMimeType();

            $path = $file->getRealPath();
            $csvData = array_map('str_getcsv', file($path));

            if (empty($csvData) || count($csvData) < 2) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.invalid_csv_no_data'));
            }

            $fields = $this->getFieldsByListId($list_id);
            if ($fields->isEmpty()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $list_id, trans('message.no_field_definitions_found'));
            }

            $expectedHeaders = $fields->pluck('field_label')->toArray();
            $csvHeaders = array_map('trim', $csvData[0]);

            if (!$this->validateCsvHeaders($csvHeaders, $expectedHeaders)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.csv_headers_mismatch'));
            }

            unset($csvData[0]);
            $validData = $this->prepareDataForInsertion($csvData, $expectedHeaders);

            return $this->insertDataIntoTable($list_id, $validData, $fileName, $fileType);
        } catch (\Exception $e) {
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function createCsvResponse(array $headers, $list_id)
    {
        $response = new StreamedResponse(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="sample_list_' . $list_id . '.csv"');

        return $response;
    }

    private function logImportHistory($list_id, $successCount, $failCount, $fileName, $fileType)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // If the user is authenticated, retrieve the user ID
        if ($user) {
            $userId = $user->id;
        } else {
            // Handle case when user is not found (invalid token or unauthenticated)
            $userId = null;
        }

        // Prepare the data to store in the history table
        $historyData = [
            'user_id' => $userId,
            'file_name' => $fileName, // Or any logic to store file name
            'file_type' => $fileType, // Adjust this as necessary
            'import_success_count' => $successCount,
            'import_fail_count' => $failCount,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into the lead_import_histories table
        LeadImportHistory::create($historyData);
    }
}
