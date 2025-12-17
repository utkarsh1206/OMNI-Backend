<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel 11 native way to get tables
        $tables = Schema::getTableListing();

        foreach ($tables as $tableName) {
            if (str_starts_with($tableName, 'custom_lead_')) {

                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->unsignedBigInteger('original_lead_id')
                        ->nullable()
                        ->after('id');

                    $table->foreign('original_lead_id')
                        ->references('id')
                        ->on($tableName)
                        ->onDelete('cascade');
                });

                // Self-reference existing rows
                DB::table($tableName)->update([
                    'original_lead_id' => DB::raw('id')
                ]);
            }
        }
    }

    public function down(): void
    {
        $tables = Schema::getTableListing();

        foreach ($tables as $tableName) {
            if (str_starts_with($tableName, 'custom_lead_')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['original_lead_id']);
                    $table->dropColumn('original_lead_id');
                });
            }
        }
    }
};
