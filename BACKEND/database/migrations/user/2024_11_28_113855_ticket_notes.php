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
        Schema::create('support_ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supportticket_id');
            $table->longText('note');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('date')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
