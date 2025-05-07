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
        Schema::table('messages', function (Blueprint $table) {
            $table->integer('receiver_id')->nullable()->after('user_id');
            $table->enum('type', ['user_chat', 'agent_chat'])->after('receiver_id')->default('user_chat');
            $table->string('sender')->nullable()->change(); // Assuming sender is a string column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['receiver_id', 'type']);
            $table->string('sender')->nullable(false)->change(); 
        });
    }
};
