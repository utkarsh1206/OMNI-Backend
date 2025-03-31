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
        Schema::create('neox_temp_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_unique_id', 250)->nullable();
            $table->string('campaign_id', 250)->nullable();
            $table->string('caller_username', 250)->nullable();
            $table->integer('caller_user_extension')->nullable();
            $table->string('user_group', 255)->nullable();
            $table->string('inbound_group_id', 200)->nullable();
            $table->string('called_phone_number', 200)->nullable();
            $table->string('type', 200)->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->enum('call_initiated_status', ['read', 'unread'])->default('unread');
            $table->enum('call_connected_status', ['read', 'unread'])->default('unread');
            $table->enum('call_hangup_status', ['read', 'unread'])->default('unread');
            $table->enum('call_type', ['incoming', 'outgoing'])->nullable();
            $table->longText('json_data')->nullable()->collation('utf8mb4_bin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neox_temp_call_logs');
    }
};
