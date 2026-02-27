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
            // 1. Speeds up unread count: (user_id, receiver_id, is_read)
            $table->index(['user_id', 'receiver_id', 'is_read'], 'idx_msgs_unread_count');

            // 2. Speeds up latest individual message: (receiver_id, user_id, created_at DESC)
            $table->index(['receiver_id', 'user_id', 'created_at'], 'idx_msgs_latest_indiv');

            // 3. Speeds up latest group message: (group_id, created_at DESC)
            $table->index(['group_id', 'created_at'], 'idx_msgs_latest_group');

            // // Check driver and version
            // $driver = DB::getDriverName();
            // $version = DB::selectOne('SELECT VERSION() as v')->v;

            // $isMySQL8 = $driver === 'mysql' && version_compare($version, '8.0.0', '>=');

            // if ($isMySQL8) {
            //     // MySQL 8.0+ supports DESC indexes
            //     DB::statement('CREATE INDEX idx_msgs_latest_indiv 
            //                ON messages (receiver_id ASC, user_id ASC, created_at DESC)');
            //     DB::statement('CREATE INDEX idx_msgs_latest_group 
            //                ON messages (group_id ASC, created_at DESC)');
            // } else {
            //     // Fallback for MariaDB / MySQL < 8.0
            //     Schema::table('messages', function (Blueprint $table) {
            //         $table->index(['receiver_id', 'user_id', 'created_at'], 'idx_msgs_latest_indiv');
            //         $table->index(['group_id', 'created_at'], 'idx_msgs_latest_group');
            //     });
            // }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_msgs_unread_count');
            $table->dropIndex('idx_msgs_latest_indiv');
            $table->dropIndex('idx_msgs_latest_group');
        });
    }
};
