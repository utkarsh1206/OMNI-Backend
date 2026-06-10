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
        if (Schema::hasTable('api_button_config_mappings')) {
            return;
        }

        Schema::create('api_button_config_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('button_key', 100)->index();
            $table->foreignId('api_config_id')->constrained('api_configs')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('display_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['button_key', 'api_config_id'], 'uniq_button_api_config');
            $table->index(['button_key', 'is_active'], 'idx_button_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_button_config_mappings');
    }
};
