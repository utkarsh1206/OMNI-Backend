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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->integer('account_id')->nullable();
            $table->string('name_prefix', 20)->default('Mr');
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('email')->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Transgender'])->nullable();
            $table->integer('owner_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('pincode')->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->enum('is_delete', ['No', 'Yes'])->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('contacts');
    }
};
