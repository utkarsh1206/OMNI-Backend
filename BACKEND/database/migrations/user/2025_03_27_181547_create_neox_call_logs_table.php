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
        Schema::create('neox_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_unique_id', 250);
            $table->string('campaign_id', 250)->nullable();
            $table->string('inbound_group_id', 250)->nullable();
            $table->string('user_group', 250)->nullable();
            $table->dateTime('call_date')->nullable();
            $table->string('caller_username', 250)->nullable();
            $table->string('caller_user_extension', 200)->nullable();
            $table->bigInteger('called_phone_number')->nullable();
            $table->string('call_total_duration', 100)->nullable();
            $table->integer('call_talk_duration')->nullable();
            $table->string('call_status', 100)->nullable();
            $table->string('call_dispo_status', 100)->nullable();
            $table->string('call_term_reason', 100)->nullable();
            $table->dateTime('cust_call_time')->nullable();
            $table->dateTime('aband_call_time')->nullable();
            $table->text('comments')->nullable();
            $table->string('call_did_pattern', 15)->nullable();
            $table->text('call_recording_url')->nullable();
            $table->string('type', 100)->nullable();
            $table->enum('call_type', ['incoming', 'outgoing', 'vb'])->nullable();
            $table->longText('json_data')->nullable()->collation('utf8mb4_bin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neox_call_logs');
    }
};
