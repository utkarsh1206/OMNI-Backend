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
            $table->dropColumn([
                'chat_id',
                'agent_id',
                'customer_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->tinyInteger('status')->nullable()->default(1)->comment('1 => Active, 2 => Disconnected');
        });
    }
};
