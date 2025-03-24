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
        Schema::table('supportticket', function (Blueprint $table) {
            $table->dropColumn('is_delete');
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supportticket', function (Blueprint $table) {
            $table->boolean('is_delete')->default(false);
            $table->dropSoftDeletes();
        });
    }
};
