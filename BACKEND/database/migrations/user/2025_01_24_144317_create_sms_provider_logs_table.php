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
        Schema::create('sms_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->integer("provider_id");
            $table->integer("message_id");
            $table->enum('status', ['sent', 'failed', 'pending'])->default('sent');
            $table->text("error_message")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_provider_logs');
    }
};
