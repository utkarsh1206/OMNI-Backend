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
        Schema::table('email_configures', function (Blueprint $table) {
            // Remove 'inbound_id' column
            $table->dropColumn('inbound_id');

            // Rename 'campaign_id' to 'department_id'
            $table->renameColumn('campaign_id', 'department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            // Rollback: Add 'inbound_id' column back (assuming it was an integer and nullable)
            $table->integer('inbound_id')->nullable();

            // Rollback: Rename 'department_id' back to 'campaign_id'
            $table->renameColumn('department_id', 'campaign_id');
        });
    }
};
