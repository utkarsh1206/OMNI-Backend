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
        Schema::table('admins', function (Blueprint $table) {
            // Remove the 'is_delete' column
            $table->dropColumn('is_delete');

            // Add the 'softDeletes' column
            $table->softDeletes(); // This adds a 'deleted_at' column for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            // Re-add the 'is_delete' column (assuming it's a boolean)
            $table->boolean('is_delete')->default(false);

            // Drop the 'deleted_at' column (used for soft deletes)
            $table->dropSoftDeletes();
        });
    }
};
