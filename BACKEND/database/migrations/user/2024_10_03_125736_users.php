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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing ID column
            $table->unsignedBigInteger('tenant_id');
            $table->integer('type')->default(0);
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->bigInteger('phone')->unsigned()->nullable();
            $table->string('phone_password')->nullable();
            $table->string('domain')->nullable();
            $table->bigInteger('mobile')->unsigned()->nullable();
            $table->integer('status')->default(0);
            $table->integer('is_deleted')->default(0);
            $table->integer('role_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
