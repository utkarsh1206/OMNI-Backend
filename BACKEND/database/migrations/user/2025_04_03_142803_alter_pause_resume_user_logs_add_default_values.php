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
        Schema::table('pause_resume_user_logs', function (Blueprint $table) {
            $table->time('pause_time')->default('00:00:00')->change();
            $table->time('resume_time')->default('00:00:00')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pause_resume_user_logs', function (Blueprint $table) {
            //
        });
    }
};
