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
        Schema::create('ecdial_lists_fields', function (Blueprint $table) {
            $table->id(); // Primary Key, Auto Increment
            $table->foreignId('list_id')->constrained('ecdial_lists', 'list_id')->onDelete('cascade');
            $table->string('field_name', 255);
            $table->string('field_label', 255);
            $table->enum('field_type', ['text', 'select', 'multi', 'checkbox', 'radio', 'textarea', 'date'])->default('text');
            $table->string('description', 255)->nullable();
            $table->string('options', 255)->nullable();
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
            $table->string('default_value', 255)->nullable();
            $table->enum('value_masked', ['Y', 'N'])->default('N');
            $table->enum('duplicate_check', ['Y', 'N'])->default('N');
            $table->timestamps();

            $table->unique(['list_id', 'field_name']);
            $table->unique(['list_id', 'field_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_lists_fields');
    }
};
