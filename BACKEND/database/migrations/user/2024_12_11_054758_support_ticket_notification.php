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
        Schema::create('support_ticket_notification', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('type'); // String column for notification type
            $table->string('status', 255)->nullable();
            $table->text('comment');
            $table->unsignedBigInteger('support_ticket_id'); // Foreign key to support_ticket table
            $table->integer('created_by')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->dateTime('date'); // Date and time of the notification
            $table->timestamps(); // Includes created_at and updated_at timestamps
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
