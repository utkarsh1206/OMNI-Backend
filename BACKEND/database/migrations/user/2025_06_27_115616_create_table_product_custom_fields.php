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
        Schema::create('product_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('field_name');
            $table->string('field_label');
            $table->enum('field_type', ['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date'])->default('text');
            $table->string('description')->nullable();
            $table->string('options')->nullable();
            $table->enum('mandatory', ['Y', 'N'])->default('N');
            $table->enum('calling_authority', ['Y', 'N'])->default('N');
            $table->enum('show_authority', ['Y', 'N'])->default('N');
            $table->enum('agent_view', ['Y', 'N'])->default('Y');
            $table->integer('field_order');
            $table->text('field_help')->nullable();
            $table->integer('field_size')->nullable();
            $table->integer('field_max_length')->nullable();
            $table->enum('search_filter', ['Y', 'N'])->default('N');
            $table->enum('editable', ['Y', 'N'])->default('N');
            $table->string('default_value')->nullable();
            $table->enum('value_masked', ['Y', 'N'])->default('N');
            $table->enum('duplicate_check', ['Y', 'N'])->default('N');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->enum('field_column', ['A', 'B', 'C', 'D'])->nullable();
            $table->string('section_name');

            // Constraints
            $table->unique(['product_id', 'field_name']);
            $table->unique(
                ['product_id', 'field_column', 'field_order', 'section_name'],
                'product_custom_fields_unique_idx'
            );

            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_custom_fields');
    }
};
