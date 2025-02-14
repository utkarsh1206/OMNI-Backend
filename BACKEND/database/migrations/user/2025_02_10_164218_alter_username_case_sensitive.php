<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Change the collation of the username column to be case-sensitive
        DB::statement("ALTER TABLE users MODIFY username VARCHAR(255) BINARY NOT NULL;");
    }

    public function down(): void
    {
        // Revert back to case-insensitive collation
        DB::statement("ALTER TABLE users MODIFY username VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }
};
