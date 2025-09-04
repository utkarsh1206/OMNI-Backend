<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = DB::select('SHOW TABLES');

        $prefix = 'custom_lead_';
        $columnName = 'omni_lead_source';

        foreach ($tables as $table) {
            // Extract the table name dynamically based on the database driver
            $tableName = current((array) $table);

            if (str_starts_with($tableName, $prefix)) {
                // Check if the column already exists
                if (!Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        $table->string($columnName, 255)->nullable();
                        // You can add ->after('some_other_column') if you want to place it at a specific position
                    });
                    // generateLogs('info', "Added column '{$columnName}' to table '{$tableName}'.");
                } else {
                    // generateLogs('info', "Column '{$columnName}' already exists in table '{$tableName}'. Skipping.");
                    // DO NOTHING
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = DB::select('SHOW TABLES');
        $prefix = 'custom_lead_';
        $columnName = 'omni_lead_source';

        foreach ($tables as $table) {
            $tableName = current((array) $table);

            if (str_starts_with($tableName, $prefix)) {
                // Only drop the column if it exists to prevent errors
                if (Schema::hasColumn($tableName, $columnName)) {
                    Schema::table($tableName, function (Blueprint $table) use ($columnName) {
                        $table->dropColumn($columnName);
                    });
                    // generateLogs('info', "Dropped column '{$columnName}' from table '{$tableName}'.");
                }
            }
        }
    }
};
