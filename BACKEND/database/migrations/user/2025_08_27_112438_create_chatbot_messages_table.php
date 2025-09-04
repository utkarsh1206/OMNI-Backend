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
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('agent_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->uuid('session_id')->nullable();
            $table->string('sender')->nullable();
            $table->timestamp('external_message_time')->nullable();
//          $table->string('first_name')->nullable();
//          $table->string('last_name')->nullable();
            $table->text('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
