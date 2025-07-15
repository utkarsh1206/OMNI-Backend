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
        Schema::table('support_ticket_custom_field_values', function (Blueprint $table) {
            $table->dropForeign(['product_id', 'field_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_custom_field_values', function (Blueprint $table) {
            $table->foreign(['product_id', 'field_name'])
                ->references(['product_id', 'field_name'])
                ->on('product_custom_fields');
        });
    }
};
