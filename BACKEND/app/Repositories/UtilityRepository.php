<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Download;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UtilityRepository
{
    private $apiResponse, $domain, $authId;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
        $this->domain = auth()->user()->domain;
        $this->authId = auth()->user()->id;
    }

    public function export()
    {
        try {
            $database = auth()->user()->db ?? 'omnichannel';
            $backupFolder = storage_path('app/public/exports');
            $filename = 'db_backup_' . now()->format('Ymd_His') . '.sql';
            $filePath = $backupFolder . '/' . $filename;

            if (!file_exists($backupFolder)) {
                mkdir($backupFolder, 0755, true);
            }

            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;

            $sqlContent = '';

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Get the CREATE TABLE statement
                $createTableRow = DB::selectOne("SHOW CREATE TABLE `$tableName`");
                $createTableStatement = $createTableRow->{'Create Table'};

                $sqlContent .= "-- ----------------------------\n";
                $sqlContent .= "-- Table structure for `$tableName`\n";
                $sqlContent .= "-- ----------------------------\n";
                $sqlContent .= "DROP TABLE IF EXISTS `$tableName`;\n";
                $sqlContent .= $createTableStatement . ";\n\n";

                // Get the data of the table
                $rows = DB::table($tableName)->get();
                if ($rows->isNotEmpty()) {
                    $sqlContent .= "-- ----------------------------\n";
                    $sqlContent .= "-- Data for table `$tableName`\n";
                    $sqlContent .= "-- ----------------------------\n";
                }

                $batchSize = 10;
                $batch = [];
                $columns = [];
                $now = now()->format('Y-m-d H:i:s');
                
                foreach ($rows as $index => $row) {
                    $row = (array) $row;

                    if (array_key_exists('created_at', $row)) {
                        $row['created_at'] = $row['created_at'] ?? $now;
                    }
                    if (array_key_exists('updated_at', $row)) {
                        $row['updated_at'] = $row['updated_at'] ?? $now;
                    }

                    if (empty($columns)) {
                        $columns = array_keys($row);
                    }

                    $escapedValues = array_map(function ($value) {
                        return DB::getPdo()->quote($value);
                    }, array_values($row));

                    $batch[] = '(' . implode(', ', $escapedValues) . ')';

                    // Write batch when full or on last item
                    if (count($batch) === $batchSize || $index === $rows->count() - 1) {
                        $columnsList = '`' . implode('`, `', $columns) . '`';
                        $sqlContent .= "INSERT INTO `$tableName` ($columnsList) VALUES\n";
                        $sqlContent .= implode(",\n", $batch) . ";\n";
                        $batch = [];
                    }
                }
                $sqlContent .= "\n\n";
            }

            file_put_contents($filePath, $sqlContent);

            // File size for logging
            $fileSizeBytes = filesize($filePath);
            $fileSize = $fileSizeBytes > 1048576
                ? number_format($fileSizeBytes / 1048576, 1) . ' MB'
                : number_format($fileSizeBytes / 1024, 2) . ' KB';

            // Activity logging
            $activityData = [
                "EDIT_ID" => md5(rand()),
                'DOMAIN' => $this->domain,
                'USER_ID' => $this->authId,
                'DESCRIPTION' => 'Database export',
                'ACTIVITY_DATETIME' => now(),
                'ACTIVITY_TYPE' => 'Export',
                'ACTIVITY_FROM' => 'System',
                'IS_SUPERADMIN' => 'Yes',
                'DATABASE_FILE_SIZE' => $fileSize,
                'IS_DELETE' => 'No',
                'DATABASE_FILES' => $filename,
                'APPLICATION_FILE' => null,
                'APPLICATION_FILE_SIZE' => null,
                'CREATED' => now(),
            ];
            // Log or store in DB if needed
            generateLogs('info', 'Database export complete:  ' . json_encode(['activityData' => $activityData]));

            $fileDetails = [
                'file_name' => $filename,
                'file_size' => $fileSize,
            ];

            $this->store($fileDetails, 'Export');

            return $this->apiResponse->getResponseStructure(trans('message.success'), [], trans('message.export_success'));

        } catch (Exception $ex) {
            generateLogs('error', 'Error in Database Export: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function exportUtility(Request $request)
    {
        return $this->databaseListing($request, 'Export');
    }

    public function import(Request $request)
    {
        try {
            // Get the uploaded file
            // $file = $request->file('import_file');
            // $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // $extension = $file->getClientOriginalExtension();
            // $timestamp = now()->format('Ymd_His');

            // // New filename: user_fileName_TIMESTAMP.sql
            // $newFilename = $originalName . '_' . $timestamp . '.' . $extension;

            $importPath = storage_path('app'.DIRECTORY_SEPARATOR.'import');

            // Create the directory if it doesn't exist
            if (!File::exists($importPath)) {
                File::makeDirectory($importPath, 0755, true);
            }

            $file = uploadAttachment($request, 'import_file', 'import');    
           
            $fullFilePath = storage_path('app/public'. DIRECTORY_SEPARATOR . $file['path']);
            
            // Read and execute SQL queries line by line
            $templine = '';
            foreach (File::lines($fullFilePath) as $line) {
                $trimmedLine = trim($line);

                // Skip comments and empty lines
                if (Str::startsWith($trimmedLine, '--') || $trimmedLine === '') {
                    continue;
                }

                $templine .= $line;

                // Execute when a statement ends
                if (Str::endsWith(trim($line), ';')) {
                    DB::unprepared($templine);
                    $templine = '';
                }
            }

            // Calculate file size
            $fileSizeBytes = filesize($fullFilePath);
            $fileSize = $fileSizeBytes > 1048576
                ? number_format($fileSizeBytes / 1048576, 2) . ' MB'
                : number_format($fileSizeBytes / 1024, 2) . ' KB';

            // Prepare activity log
            $activityData = [
                'EDIT_ID' => md5(rand()),
                'DOMAIN' => $this->domain,
                'USER_ID' => $this->authId,
                'DESCRIPTION' => 'Database Import',
                'ACTIVITY_DATETIME' => now(),
                'ACTIVITY_TYPE' => 'Import',
                'IS_SUPERADMIN' => 'Yes',
                'FILE_SIZE' => $fileSize,
                'IS_DELETE' => 'No',
                'FILES' => $$file['fileName'],
                'CREATED' => now(),
            ];

            generateLogs('info', 'Database import complete: ' . json_encode(['activityData' => $activityData]));
            
            $fileDetails = [
                'file_name' => $$file['fileName'],
                'file_size' => $fileSize,
            ];

            $this->store($fileDetails, 'Import');

            return $this->apiResponse->getResponseStructure(trans('message.success'), [], trans('message.import_success'));
        } catch (Exception $ex) {
            dd($ex->getMessage());
            generateLogs('error', 'Error in Database Import: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function importUtility(Request $request)
    {
        return $this->databaseListing($request, 'Import');
    }

    private function databaseListing($request, $type)
    {
        try {
            $searchTerm = $request->input('search', '');

            // Start the query for tenants
            $query = Download::select('id', 'file_name', 'file_size', 'user_id', 'created_at')->where('type', $type);

            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('file_name', 'like', '%' . $searchTerm . '%');
                });
            }

            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));

            $data = $page === config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);

            $data->getCollection()->transform(function ($item) use ($type) {
                if (strtolower($type) === 'import') {
                    // Remove only 'full_file_path' for Import
                    $item->setHidden(['user', 'full_file_path']);
                }
                // Username always exists
                return $item;
            });

            return $this->apiResponse->getResponseStructure(trans('message.success'), $data, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Database List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }

    private function store($file, $type)
    {
        try {
            return Download::create([
                'file_name' => $file['file_name'],
                'file_size' => $file['file_size'],
                'user_id' => $this->authId,
                'type' => $type
            ]);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Storing Database Details: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
