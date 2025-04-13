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
            $table->enum('field_column', ['A', 'B', 'C', 'D'])->nullable();

            // Drop the old unique constraint
            $table->dropUnique('ecdial_lists_fields_list_id_field_order_unique');

            // Add the new unique constraint
            $table->unique(['list_id', 'field_column', 'field_order'], 'ecdial_lists_fields_list_id_field_column_field_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('ecdial_lists_fields_list_id_field_column_field_order');
            // Recreate the old unique constraint
            $table->unique(['list_id', 'field_order'], 'ecdial_lists_fields_list_id_field_order_unique');

            // Drop the added column
            $table->dropColumn('field_column');
        });
    }
};
