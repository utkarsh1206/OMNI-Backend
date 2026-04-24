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
        Schema::create('auth_providers', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();

            $table->enum('type', [
                'none',
                'api_key',
                'basic',
                'bearer_token',
                'oauth2',
                'hmac',
                'jwt'
            ])->index();

            $table->text('token_url')->nullable();
            $table->enum('method', ['GET', 'POST', 'PUT', 'DELETE'])->nullable();

            // Flexible credentials storage
            $table->json('credentials')->nullable();

            // Request config for token API (headers, body, query)
            $table->json('request_config')->nullable();

            // Mapping for extracting values from response
            $table->json('response_mapping')->nullable();

            // Expiry handling
            $table->enum('expiry_type', ['seconds', 'timestamp'])->nullable();

            // Rules to detect expired token
            $table->json('token_expiry_rules')->nullable();

            $table->boolean('token_cache_enabled')->default(true);
            $table->boolean('get_on_each_request')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_providers');
    }
};
