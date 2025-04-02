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
        Schema::create('pause_resume_user_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->string('campaign_id');
            $table->integer('user_id');
            $table->integer('pause_code_id')->nullable();
            $table->enum('user_status', ['Pause', 'Resume', 'Disposition', 'Logout', 'Start Call', 'End Call'])->default('Logout');
            $table->time('pause_time');
            $table->time('resume_time');
            $table->time('total_pause_time')->nullable();
            $table->time('total_resume_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pause_resume_user_logs');
    }
};
