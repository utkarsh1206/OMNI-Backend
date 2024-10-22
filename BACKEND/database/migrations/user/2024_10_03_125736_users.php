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
            //$table->unsignedBigInteger('tenant_id');
            $table->integer('type')->default(0);
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('domain')->nullable();
            $table->bigInteger('mobile')->unsigned()->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('is_delete', ['Yes', 'No'])->default('No');
            $table->integer('role_id')->default(0);
            $table->integer('usergroup_id')->default(0);
            $table->string('name_prefix')->nullable();
            $table->string('level')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('occupation_id')->nullable();
            $table->string('pincode')->nullable();
            $table->text('address')->nullable();
            $table->enum('gender', ['MALE', 'FEMALE', 'TRANSGENDER'])->nullable();
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
