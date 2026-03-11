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
        Schema::table('email_schedules', function (Blueprint $table) {
            $table->string('timezone')->nullable()->default(null)->change();
            $table->enum('frequency', [
                'daily',
                'weekly',
                'monthly',
                'quarterly',
                'half_yearly',
                'yearly',
                'custom'
            ])->change();
            $table->unsignedTinyInteger('monthly_date')->nullable()->after('day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_schedules', function (Blueprint $table) {
            $table->string('timezone')->default('Asia/Kolkata')->nullable(false)->change();
            $table->enum('frequency', [
                'daily',
                'weekly',
                'quarterly',
                'half_yearly',
                'yearly',
                'custom'
            ])->change();
            $table->dropColumn('monthly_date');
        });
    }
};
