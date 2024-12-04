<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;

class ImportSQLFiles extends Command
{
    protected $signature = 'import:sql-files';
    protected $description = 'Import all SQL files into the database';

    public function handle()
    {
        // Directory path to SQL files
        $directoryPath = storage_path('app/sql/');

        // Get all SQL files in the directory
        $files = File::files($directoryPath);

        // Filter only .sql files
        $sqlFiles = array_filter($files, function ($file) {
            return $file->getExtension() === 'sql';
        });

        // Check if there are any SQL files to process
        if (empty($sqlFiles)) {
            $this->info("No SQL files found in the directory.");
            return;
        }

        // Loop through each file and import
        foreach ($sqlFiles as $file) {
            $this->info("Processing file: {$file->getFilename()}");

            // Read the SQL file content
            $sql = file_get_contents($file->getPathname());

            // Split the content into individual queries
            $queries = explode(';', $sql);

            foreach ($queries as $query) {
                if (trim($query)) {
                    try {
                        DB::unprepared($query);  // Execute each query
                    } catch (QueryException $e) {
                        // Check if the error is because the table already exists
                        if (strpos($e->getMessage(), 'already exists') !== false) {
                            $this->info("Table already exists, skipping query.");
                        } else {
                            // Log any other type of error
                            $this->error("Error executing query: {$e->getMessage()}");
                        }
                    }
                }
            }

            $this->info("SQL file {$file->getFilename()} imported successfully.");
        }
    }
}
