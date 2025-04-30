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
        Schema::create('ecdial_evaluation_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question')->unique();
            $table->enum('question_type', ['TEXT', 'RADIO', 'DROPDOWN', 'MULTI'])->default('TEXT');
            $table->text('answers')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys (adjust according to your user table)
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_evaluation_questions');
    }
};
