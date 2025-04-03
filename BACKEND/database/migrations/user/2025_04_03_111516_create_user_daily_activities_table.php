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
        Schema::create('user_daily_activities', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_id');
            $table->integer('user_id');
            $table->string('call_unique_id', 200)->nullable();
            $table->string('activity', 100);
            $table->string('pause_code', 100)->nullable();
            $table->time('pause_duration')->default('00:00:00');
            $table->time('resume_duration')->default('00:00:00');
            $table->time('wait_duration')->default('00:00:00');
            $table->time('call_duration')->default('00:00:00');
            $table->time('talk_duration')->default('00:00:00');
            $table->time('dispo_duration')->default('00:00:00');
            $table->time('total_login_duration')->default('00:00:00');
            $table->string('dispo_code', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_activities');
    }
};
