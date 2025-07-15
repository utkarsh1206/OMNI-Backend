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
        Schema::create('evaluation_ticket_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id', 40);
            $table->string('user_id', 320);
            $table->string('bunch_name', 320)->nullable();
            $table->text('questions')->nullable();
            $table->text('selected_answer')->nullable();
            $table->string('comment', 320)->nullable();
            $table->char('ticket_rating', 1)->default('0');
            $table->string('created_by', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_ticket_reviews');
    }
};
