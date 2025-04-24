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
            $table->string('room_id')->nullable()->after('is_read');
            $table->string('sender')->after('room_id');
            $table->timestamp('external_message_time')->after('sender');
            $table->string('node_type')->nullable()->after('external_message_time');
            $table->uuid('session_id')->nullable()->after('node_type');
            $table->string('first_name')->nullable()->after('session_id');
            $table->string('last_name')->nullable()->after('first_name');

            $table->integer('user_id')->nullable();
            $table->text('text')->nullable();
            $table->string('attachment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('room_id');
            $table->dropColumn('sender');
            $table->dropColumn('external_message_time');
            $table->dropColumn('node_type');
            $table->dropColumn('session_id');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
        });
    }
};
