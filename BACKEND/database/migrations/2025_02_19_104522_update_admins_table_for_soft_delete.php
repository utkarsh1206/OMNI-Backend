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
            $table->dropColumn('edit_id');
            $table->dropColumn('is_delete');
            $table->dropColumn('created');
            $table->dropColumn('modified');

            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
        $table->boolean('is_delete')->default(false);

            // Drop the 'deleted_at' column (used for soft deletes)
            $table->dropSoftDeletes();
        });
    }
};
