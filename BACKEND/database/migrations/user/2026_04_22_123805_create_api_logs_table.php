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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('api_config_id')
                ->constrained('api_configs')
                ->cascadeOnDelete();

            $table->json('request')->nullable();
            $table->json('response')->nullable();

            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->boolean('success')->default(false)->index();

            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
