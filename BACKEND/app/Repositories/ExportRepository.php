<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Account;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ExportRepository
{
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->apiResponse = $customApiResponse;
    }

    public function export()
    {
        try {
            $database = auth()->user()->domain ?? 'omnichannel';
            $backupFolder = storage_path('app/exports');
            $zipFilename = 'database_backup_' . now()->format('YmdHis') . '.zip';
            $zipFilePath = $backupFolder . '/' . $zipFilename;

            if (!file_exists($backupFolder)) {
                mkdir($backupFolder, 0755, true);
            }
            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $database;
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    $createTableRow = DB::selectOne("SHOW CREATE TABLE `$tableName`");
                    $createTableStatement = $createTableRow->{'Create Table'};
                    $rows = DB::table($tableName)->get();
                    $insertStatements = [];
                    foreach ($rows as $row) {
                        $columns = array_keys((array) $row);
                        $values = array_map(function ($value) {
                            return DB::getPdo()->quote($value);
                        }, (array) $row);

                        $insertStatements[] = "INSERT INTO `$tableName` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                    }

                    $sqlContent = $createTableStatement . PHP_EOL . implode(PHP_EOL, $insertStatements);
                    $zip->addFromString("$tableName.sql", $sqlContent);
                }

                $zip->close();
            } else {
                return response()->json(['error' => 'Failed to create ZIP archive.'], 500);
            }
            // File size for logging
            $fileSizeBytes = filesize($zipFilePath);
            $fileSize = $fileSizeBytes > 1048576
                ? number_format($fileSizeBytes / 1048576, 1) . ' MB'
                : number_format($fileSizeBytes / 1024, 2) . ' KB';
            // Activity logging
            $activityData = [
                "EDIT_ID" => md5(rand()),
                'USER_ID' => auth()->user()->id,
                'DESCRIPTION' => 'Database export',
                'ACTIVITY_DATETIME' => now(),
                'ACTIVITY_TYPE' => 'Export',
                'ACTIVITY_FROM' => 'System',
                'IS_SUPERADMIN' => 'Yes',
                'DATABASE_FILE_SIZE' => $fileSize,
                'IS_DELETE' => 'No',
                'DATABASE_FILES' => $zipFilename,
                'APPLICATION_FILE' => null,
                'APPLICATION_FILE_SIZE' => null,
                'CREATED' => now(),
            ];

            // Log or store in DB if needed
            Log::info('Database export complete.', $activityData);
            return response()->download($zipFilePath, $zipFilename)->deleteFileAfterSend(true);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Database Export: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }
}
