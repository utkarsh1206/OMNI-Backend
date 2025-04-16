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
        Schema::create('transfered_call_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('campaign_id');
            $table->string('call_unique_id', 250);
            $table->string('mobile_username', 250)->nullable();
            $table->string('mobile_number', 250);
            $table->integer('transfer_from');
            $table->string('transfer_to')->nullable();
            $table->string('transfer_to_type', 25)->nullable();
            $table->dateTime('call_date');
            $table->string('call_direction', 250);
            $table->string('transfer_type', 15);
            $table->string('call_route_data', 25)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfered_call_logs');
    }
};
