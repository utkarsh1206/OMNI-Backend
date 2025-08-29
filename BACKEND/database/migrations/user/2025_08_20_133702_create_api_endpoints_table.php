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
        Schema::create('api_endpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('list_id');
            $table->string('url');
            $table->enum('method', ['GET', 'POST'])->default('GET');
            $table->enum('payload_type', ['json', 'query'])->default('json');
            $table->json('auth_config')->nullable();

            $table->timestamps();

            $table->foreign('list_id')->references('list_id')->on('ecdial_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_endpoints');
    }
};
