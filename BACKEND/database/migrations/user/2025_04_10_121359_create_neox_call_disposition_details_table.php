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
        Schema::create('neox_call_disposition_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('agent_neox_phone', 150)->nullable();
            $table->string('call_unique_id', 250)->nullable();
            $table->string('mobile_username', 250)->nullable();
            $table->string('mobile_number', 250)->nullable();
            $table->string('disposition_status', 250)->nullable();
            $table->text('disposition_detail')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neox_call_disposition_details');
    }
};
