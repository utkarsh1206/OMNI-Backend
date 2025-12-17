<?php

namespace App\Repositories;

use App\Models\EcdialListField;
use App\Models\LeadImportHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Response\CustomApiResponse;
use App\Models\EcdialLists;
use Carbon\Carbon;
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
                ->orderBy('field_column')
                ->get();
        } catch (\Exception $e) {
            generateLogs('error', 'Error in generating getFieldsByListId : ', $e->getMessage());
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
        $invalidData = [];

        foreach ($csvData as $index => $row) {
            $rowAssoc = array_combine($expectedHeaders, array_map('trim', $row));
            $errors = [];

            // Validation rules
            if (isset($rowAssoc['First Name']) && !preg_match('/^[a-zA-Z\s\-]+$/', $rowAssoc['First Name'])) {
                $errors[] = 'Invalid First Name';
            }

            if (isset($rowAssoc['Last Name']) && !preg_match('/^[a-zA-Z\s\-]+$/', $rowAssoc['Last Name'])) {
                $errors[] = 'Invalid Last Name';
            }

            // if (isset($rowAssoc['Mobile']) && !preg_match('/^\d{10}$/', $rowAssoc['Mobile'])) {
            //     $errors[] = 'Invalid Mobile';
            // }

            if (isset($rowAssoc['Mobile']) && !preg_match('/^\+?\d{1,20}$/', $rowAssoc['Mobile'])) {
                $errors[] = 'Invalid Mobile';
            }

            if (isset($rowAssoc['Email']) && !filter_var($rowAssoc['Email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid Email';
            }

            if (empty($errors)) {
                $validData[] = $rowAssoc;
            } else {
                $invalidData[] = [
                    'row' => $rowAssoc,
                    'errors' => $errors
                ];
            }
        }

        return [
            'valid' => $validData,
            'invalid' => $invalidData
        ];
    }


    // public function insertDataIntoTable($list_id, array $validData, array $invalidData, $fileName, $fileType)
    // {
    //     $tableName = "custom_lead_" . $list_id;

    //     if (!Schema::hasTable($tableName)) {
    //         return $this->apiResponse->getResponseStructure(trans('message.error'), $tableName, trans('message.table_not_exist'));
    //     }

    //     $fields = $this->getFieldsByListId($list_id);
    //     $insertedCount = 0; // Track the number of successfully inserted rows
    //     $failedCount = 0; // Track failed insertions
    //     $importedData = [];

    //     foreach ($validData as $row) {
    //         $insertData = [];

    //         foreach ($fields as $field) {
    //             $insertData[strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $field->field_name))] = $row[$field->field_name] ?? null;
    //         }

    //         // $this->insertDataINtoList($list_id);

    //         $insertData['created_at'] = now();
    //         $insertData['updated_at'] = now();
    //         $insertData['omni_lead_source'] = 'csv';

    //         try {
    //             // DB::table($tableName)->insert($insertData);

    //             $lastInsertedId = DB::table($tableName)->insertGetId($insertData);

    //             $this->insertDataINtoList($list_id, $lastInsertedId);
    //             $insertedCount++; // Increment count on successful insertion
    //             $status = 'success';
    //         } catch (\Exception $e) {
    //             $failedCount++; // Increment failed count on error
    //             $status = 'failed';
    //             generateLogs('error', 'Error in generating failed : ', $e->getMessage());
    //             return $this->apiResponse->handleAndResponseException($e);
    //         }

    //         $importedData[] = [
    //             'row' => $row,
    //             'status' => $status
    //         ];
    //     }

    //     $data = [
    //         'imported_data' => $importedData,
    //         'invalid_data' => $invalidData, // Add failed rows with reasons
    //         'inserted_count' => $insertedCount,
    //         'failed_count' => $failedCount + count($invalidData) // include validation failures
    //     ];

    //     // Log the import history
    //     $this->logImportHistory($list_id, $insertedCount, $failedCount, $fileName, $fileType);
    //     return ($insertedCount > 0) ? $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_inserted_successfully')) : $this->apiResponse->getResponseStructure(trans('message.error'), $data, trans('message.no_data_inserted'));
    // }


    public function insertDataIntoTable($list_id, array $validData, array $invalidData, $fileName, $fileType)
    {
        // Set execution time for bulk operations
        set_time_limit(0); // Unlimited execution time
        ini_set('memory_limit', '5G'); // Adjust based on your needs

        $tableName = "custom_lead_" . $list_id;

        if (!Schema::hasTable($tableName)) {
            return $this->apiResponse->getResponseStructure(trans('message.error'), $tableName, trans('message.table_not_exist'));
        }

        $fields = $this->getFieldsByListId($list_id);
        $insertedCount = 0;
        $failedCount = 0;

        // Use chunked processing for very large datasets
        $chunkedData = array_chunk($validData, 1000); // Process 1000 rows at a time

        $importedData = [];

        foreach ($chunkedData as $chunkIndex => $chunk) {
            $batchData = [];

            foreach ($chunk as $rowIndex => $row) {
                $insertRow = [];
                foreach ($fields as $field) {
                    $insertRow[strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $field->field_name))] = $row[$field->field_name] ?? null;
                }

                $insertRow['created_at'] = now();
                $insertRow['updated_at'] = now();
                $insertRow['omni_lead_source'] = 'csv';
                $batchData[] = $insertRow;

                // Track row for response
                $importedData[] = [
                    'row' => $row,
                    'status' => 'pending'
                ];
            }

            // Insert batch
            if (!empty($batchData)) {
                $insertResult = $this->insertBatchOptimized($tableName, $batchData, $list_id, $importedData, $insertedCount, $chunkIndex);
                if (!$insertResult) {
                    $failedCount += count($batchData);
                }
            }

            // Free memory after each chunk
            unset($batchData);
            gc_collect_cycles();
        }

        $data = [
            'imported_data' => array_slice($importedData, 0, 1000), // Return only first 1000 rows for response
            'invalid_data' => array_slice($invalidData, 0, 1000),
            'inserted_count' => $insertedCount,
            'failed_count' => $failedCount + count($invalidData),
            'total_processed' => count($validData),
            'chunks_processed' => count($chunkedData)
        ];

        $this->logImportHistory($list_id, $insertedCount, $failedCount, $fileName, $fileType);

        return ($insertedCount > 0)
            ? $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_inserted_successfully'))
            : $this->apiResponse->getResponseStructure(trans('message.error'), $data, trans('message.no_data_inserted'));
    }

    private function insertBatchOptimized($tableName, $batchData, $list_id, &$importedData, &$insertedCount, $chunkIndex)
    {
        try {
            DB::beginTransaction();

            // 1. Insert main data
            DB::table($tableName)->insert($batchData);

            // 2. Get last inserted IDs
            $firstId = DB::getPdo()->lastInsertId();
            $batchSize = count($batchData);

            // 3. Prepare ecdial_list data
            $gmtOffset = $this->getGmtOffset();
            $ecdialListData = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $ecdialListData[] = [
                    'lead_id' => $firstId + $i,
                    'status' => 'NEW',
                    'list_id' => $list_id,
                    'gmt_offset_now' => $gmtOffset
                ];
            }

            // 4. Insert ecdial_list data
            DB::table('ecdial_list')->insert($ecdialListData);

            DB::commit();

            // Update counters
            $insertedCount += $batchSize;

            // Update status in importedData
            $startIndex = $chunkIndex * 1000;
            for ($i = 0; $i < $batchSize; $i++) {
                if (isset($importedData[$startIndex + $i])) {
                    $importedData[$startIndex + $i]['status'] = 'success';
                }
            }

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            generateLogs('error', 'Batch insert failed at chunk ' . $chunkIndex . ': ', $e->getMessage());

            // Mark as failed
            $startIndex = $chunkIndex * 1000;
            for ($i = 0; $i < count($batchData); $i++) {
                if (isset($importedData[$startIndex + $i])) {
                    $importedData[$startIndex + $i]['status'] = 'failed';
                }
            }

            return false;
        }
    }



    private function getGmtOffset()
    {
        $timezone = config('app.timezone');
        $dateTimeZone = new \DateTimeZone($timezone);
        $now = new \DateTime('now', $dateTimeZone);
        $offsetInSeconds = $now->getOffset();
        $offsetInHours = $offsetInSeconds / 3600;
        return number_format($offsetInHours, 2, '.', '');
    }

    public function downloadSampleCsv($list_id)
    {
        try {
            $fields = $this->getFieldsByListId($list_id);

            if ($fields->isEmpty()) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), $list_id, trans('message.no_fields_found_for_list'));
            }

            $headers = $fields->pluck('field_name')->toArray();

            return $this->createCsvResponse($headers, $list_id);
        } catch (\Exception $e) {
            generateLogs('error', 'Error in downloadSampleCsv failed : ', $e->getMessage());
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

            $expectedHeaders = $fields->pluck('field_name')->toArray();
            $csvHeaders = array_map('trim', $csvData[0]);

            if (!$this->validateCsvHeaders($csvHeaders, $expectedHeaders)) {
                return $this->apiResponse->getResponseStructure(trans('message.error'), null, trans('message.csv_headers_mismatch'));
            }

            unset($csvData[0]);
            $processedData = $this->prepareDataForInsertion($csvData, $expectedHeaders);
            $validData = $processedData['valid'];
            $invalidData = $processedData['invalid'];

            return $this->insertDataIntoTable($list_id, $validData, $invalidData, $fileName, $fileType);
        } catch (\Exception $e) {
            generateLogs('error', 'Error in processCsvFile failed : ', $e->getMessage());
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
        $user = JWTAuth::user();
        $externalToken = null;

        if ($user) {
            $userId = $user->id;
        } else {
            $authJsonString = $request->get('auth_token');
            $authDataArray = json_decode($authJsonString, true);
            $filteredDataCollection = collect($authDataArray)->only([
                'id',
                'token',
                'ipaddress',
            ]);
            $externalToken = $filteredDataCollection->toJson();
            $userId = null;
        }

        // Prepare the data to store in the history table
        $historyData = [
            'user_id' => $userId,
            'external_token' => $externalToken,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'import_success_count' => $successCount,
            'import_fail_count' => $failCount,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Insert into the lead_import_histories table
        LeadImportHistory::create($historyData);
    }
}
