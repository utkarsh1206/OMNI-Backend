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
        Schema::table('usergroup', function (Blueprint $table) {
            // Rename the column campaign_id to department_id
            $table->renameColumn('campaign_id', 'department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usergroup', function (Blueprint $table) {
            // Reverse the column rename (campaign_id -> department_id)
            $table->renameColumn('department_id', 'campaign_id');
        });
    }
};
