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
        Schema::create('conference_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key to users table
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            
            $table->string('caller_username', 100)->nullable();
            $table->string('caller_user_extension', 100)->nullable();
            $table->string('call_unique_id', 50)->nullable()->unique();
            
            // parent_uid as a foreign key to users(id) per your requirement
            $table->foreignId('parent_uid')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('conf_state', 50)->nullable();
            $table->string('conf_event', 50)->nullable();
            $table->string('parent_user_id', 100)->nullable();
            $table->string('original_called_phone_number', 100)->nullable();
            
            // JSON column for parameters
            $table->json('actual_url_parameter')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conference_details');
    }
};
