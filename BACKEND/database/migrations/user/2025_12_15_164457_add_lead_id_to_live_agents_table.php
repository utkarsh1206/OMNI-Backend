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
        Schema::table('live_agents', function (Blueprint $table) {
            $table->unsignedInteger('lead_id')->default(0)->index(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_agents', function (Blueprint $table) {
            $table->dropColumn('lead_id'); 
        });
    }
};
