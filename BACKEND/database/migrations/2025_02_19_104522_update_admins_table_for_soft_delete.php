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
            $table->boolean('is_delete')->default('d9a9193805dc8832cadf31791f51949b');
            $table->boolean('is_delete')->default(false);
            $table->timestamp('created', precision: 0);
            $table->timestamp('modified', precision: 0);
        
            $table->dropSoftDeletes();
        });
    }
};
