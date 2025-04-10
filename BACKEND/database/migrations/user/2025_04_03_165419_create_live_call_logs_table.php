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
        Schema::create('live_call_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('agent_extension', 150)->nullable();
            $table->string('campaign_id', 200)->nullable();
            $table->string('call_unique_id', 200);
            $table->string('mobile_username', 200)->nullable();
            $table->string('mobile_number', 15);
            $table->dateTime('call_date');
            $table->string('call_status', 200);
            $table->enum('actual_call_status', ['Active', 'Inactive'])->default('Inactive');
            $table->enum('dispose_status', ['Pending', 'Completed'])->default('Pending');
            $table->string('call_direction', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_call_logs');
    }
};
