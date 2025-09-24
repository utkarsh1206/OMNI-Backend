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
        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('chatbot_message_request_id')
                ->nullable()
                ->after('id');

            $table->foreign('chatbot_message_request_id')
                ->references('id')
                ->on('chatbot_message_requests')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->dropForeign(['chatbot_message_request_id']);
            $table->dropColumn('chatbot_message_request_id');
        });
    }
};
