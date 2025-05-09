<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            if (Schema::hasColumn('incident_types', 'priority')) {
                $table->dropColumn('priority');
            }

            $table->foreignId('priority_id')
                  ->nullable()
                  ->after('category_id');
        });

        Schema::table('incident_types', function (Blueprint $table) {
            $table->foreign('priority_id', 'fk_incident_types_priority_id')
                  ->references('id')
                  ->on('priorities')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            $table->dropForeign('fk_incident_types_priority_id');
            $table->dropColumn('priority_id');

            if (!Schema::hasColumn('incident_types', 'priority')) {
                $table->enum('priority', ['Critical', 'High', 'Medium', 'Low', 'Normal'])
                      ->default('Medium')
                      ->after('category_id');
            }
        });
    }
};
