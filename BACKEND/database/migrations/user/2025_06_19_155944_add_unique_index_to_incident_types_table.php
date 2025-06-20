<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            //
            $table->unique(['name', 'category_id'], 'incident_types_name_category_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incident_types', function (Blueprint $table) {
            //
            $table->dropUnique('incident_types_name_category_unique');
        });
    }
};
