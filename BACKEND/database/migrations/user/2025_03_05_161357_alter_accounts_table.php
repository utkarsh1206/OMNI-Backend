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
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('campaign_id')->nullable()->change();
            $table->integer('department_id')->nullable()->after('contact_id');

            // Remove department_name
            $table->dropColumn('department_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Make campaign_id not nullable again
            $table->integer('campaign_id')->nullable(false)->change();
            
            // Remove department_id
            $table->dropColumn('department_id');
            
            // Add department_name back
            $table->string('department_name', 50)->nullable()->after('department_id');
        });
    }
};
