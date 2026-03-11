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
        Schema::create('email_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('report_name');
            $table->json('selected_columns');
            $table->string('from_email');
            $table->json('to_emails');
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->enum('frequency', [
                'daily',
                'weekly',
                'quarterly',
                'half_yearly',
                'yearly',
                'custom'
            ]);
            $table->time('scheduled_time');
            $table->string('day_of_week')->nullable(); // mon,tue (weekly)
            $table->time('data_start_time')->nullable();
            $table->time('data_end_time')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->timestamp('last_executed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_schedules');
    }
};
