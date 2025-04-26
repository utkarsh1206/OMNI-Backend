<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Http\Response\CustomApiResponse;
use App\Models\Download;
use Illuminate\Support\Facades\DB;
use Exception;

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
            $database = $this->domain ?? 'omnichannel';
            $backupFolder = storage_path('app/exports');
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
                $sqlContent .= $createTableStatement . ";\n\n";
    
                // Get the data of the table
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $columns = implode('`, `', array_keys($row));
                    $values = implode(', ', array_map(function ($value) {
                        return DB::getPdo()->quote($value);
                    }, array_values($row)));
    
                    $sqlContent .= "INSERT INTO `$tableName` (`$columns`) VALUES ($values);\n";
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
            generateLogs('info', 'Database export complete:  '.json_encode(['activityData' => $activityData]));

            Download::create([
                'user_id' => $this->authId,
                'file_name' => $filename,
                'type' => 'Export'
            ]);    

            return response()->download($filePath, $filename);
            // return response()->streamDownload(function () use ($filePath) {
            //     echo file_get_contents($filePath);
            // }, $filename, [
            //     'Content-Type' => 'application/sql',
            //     'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            // ])->deleteFileAfterSend(true);
        } catch (Exception $ex) {
            generateLogs('error', 'Error in Database Export: ', $ex->getMessage());
            return $this->apiResponse->handleAndResponseException($ex);
        }
    }

    public function exportUtility(Request $request)
    {
        return $this->databaseListing($request, 'Export');
    }

    public function import()
    {
        
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
            $query = Download::select('id', 'file_name', 'user_id')->where('type', $type);
            
            // If there's a search term, apply it to the query
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
            }
            
            $perPage = $request->input('per_page', config('omni.PER_PAGE'));
            $page = $request->input('page', config('omni.DEFAULT_PAGE'));
            
            $usergroup = $page === config('omni.ALL_PAGE')
                ? $query->get()
                : $query->paginate($perPage);
            
            return $this->apiResponse->getResponseStructure(trans('message.success'), $usergroup, trans('message.data_fetch'));
        } catch (Exception $e) {
            generateLogs('error', 'Error in Database List : ', $e->getMessage());
            return $this->apiResponse->handleAndResponseException($e);
        }
    }
}
