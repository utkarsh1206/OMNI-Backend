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
        Schema::create('api_configs', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();

            $table->string('type')->nullable(); // Custom categorization

            $table->text('base_url');
            $table->text('endpoint');

            $table->enum('method', ['GET', 'POST', 'PUT', 'DELETE']);

            $table->enum('auth_type', [
                'none',
                'api_key',
                'basic'
            ])->default('none')->index();
            $table->json('auth_config')->nullable();

            $table->foreignId('auth_provider_id')
                ->nullable()
                ->constrained('auth_providers')
                ->nullOnDelete();

            $table->json('headers')->nullable();
            $table->json('query_params')->nullable();
            $table->json('body')->nullable();

            $table->unsignedInteger('timeout')->default(30);
            $table->unsignedInteger('retry_count')->default(0);

            $table->json('response_mapping')->nullable();

            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_configs');
    }
};
