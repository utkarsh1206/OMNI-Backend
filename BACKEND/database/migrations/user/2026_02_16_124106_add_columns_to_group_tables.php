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
        Schema::table('groups', function (Blueprint $table) {
            $table->enum('type', ['normal', 'broadcast'])->default('normal')->after('name');
        });
        Schema::table('group_participants', function (Blueprint $table) {
            $table->boolean('can_send_message')->default(true)->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_participants', function (Blueprint $table) {
            $table->dropColumn('can_send_message');
        });
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
