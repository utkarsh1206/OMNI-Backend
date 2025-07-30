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
            $table->enum('send_to_crm', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->dropColumn('send_to_crm');
        });
    }
};
