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
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('auth_provider_id')
                ->constrained('auth_providers')
                ->cascadeOnDelete();

            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();

            $table->timestamp('expires_at')->nullable()->index();

            $table->json('meta')->nullable(); // full response

            $table->timestamps();

            $table->unique('auth_provider_id');     // one active token per provider
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
