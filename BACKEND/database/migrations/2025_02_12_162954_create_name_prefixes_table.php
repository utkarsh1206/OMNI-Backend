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
        Schema::create('name_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 20);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('is_delete', ['No', 'Yes'])->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('name_prefixes');
    }
};
