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
        Schema::create('mail_details', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->unsignedBigInteger('supportticket_id'); // Foreign key to supportticket table
            $table->dateTime('date')->default(now()); // Date and time in YYYY-mm-dd H:i:s format
            $table->unsignedBigInteger('user_id'); // User ID
            $table->string('type')->default('open'); // Email type
            $table->string('priority')->default('High'); // Email priority (e.g., High, Medium, Low)
            $table->string('status'); // Email status (e.g., Sent, Pending, Failed)
            $table->string('to'); // Recipients email addresses (can store as comma-separated string)
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->text('body'); // Body of the email
            $table->json('attachment')->nullable(); // Attachments (stored as JSON array)
            $table->timestamps(); // Created_at and updated_at timestamps
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
