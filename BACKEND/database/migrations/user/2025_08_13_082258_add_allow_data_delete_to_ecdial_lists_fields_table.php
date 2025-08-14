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
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->enum('allow_field_deletion', ['Y', 'N'])->default('Y');
            $table->enum('allow_data_delete', ['Y', 'N'])->default('N')->after('allow_field_deletion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->dropColumn('allow_field_deletion');
            $table->dropColumn('allow_data_delete');
        });
    }
};
