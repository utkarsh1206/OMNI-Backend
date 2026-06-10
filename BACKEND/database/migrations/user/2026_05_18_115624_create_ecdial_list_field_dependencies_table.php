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
        if (Schema::hasTable('ecdial_list_field_dependencies')) {
            return;
        }

        Schema::create('ecdial_list_field_dependencies', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('child_field_id');
            $table->unsignedBigInteger('parent_field_id')->nullable();

            $table->string('param_name', 100);
            $table->enum('param_type', ['query', 'body', 'header', 'path']);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('child_field_id')
                ->references('id')
                ->on('ecdial_lists_fields')
                ->cascadeOnDelete();

            $table->foreign('parent_field_id')
                ->references('id')
                ->on('ecdial_lists_fields')
                ->nullOnDelete();

            $table->unique(['child_field_id', 'param_name', 'param_type'], 'uniq_field_dependency_param');
            $table->index(['child_field_id', 'sort_order'], 'idx_field_dependency_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_list_field_dependencies');
    }
};
