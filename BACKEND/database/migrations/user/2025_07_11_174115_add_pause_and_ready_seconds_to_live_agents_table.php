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
            $table->unsignedBigInteger('pause_seconds')->default(0);
            $table->unsignedBigInteger('ready_seconds')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_agents', function (Blueprint $table) {
            $table->dropColumn(['pause_seconds', 'ready_seconds']);
        });
    }
};
