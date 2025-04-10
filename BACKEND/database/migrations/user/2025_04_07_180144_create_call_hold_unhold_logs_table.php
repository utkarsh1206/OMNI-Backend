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
        Schema::create('call_hold_unhold_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('call_unique_id');
            $table->string('mobile_username')->nullable();
            $table->string('mobile_number')->nullable();
            $table->timestamp('call_date');
            $table->string('call_status');
            $table->string('call_direction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_hold_unhold_logs');
    }
};
