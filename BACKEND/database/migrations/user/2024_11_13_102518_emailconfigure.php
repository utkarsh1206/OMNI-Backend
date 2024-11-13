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
        Schema::create('email_configures', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->enum('type', ['gmail', 'office365', 'custom', 'zoho']); // Type of email service
            $table->unsignedBigInteger('product_id'); // Foreign key for product
            $table->unsignedBigInteger('campaign_id'); // Foreign key for campaign
            $table->unsignedBigInteger('inbound_id'); // Foreign key for inbound
            $table->string('smtp_host'); // SMTP host
            $table->integer('smtp_port'); // SMTP port
            $table->string('smtp_user'); // SMTP user
            $table->string('smtp_password'); // SMTP password
            $table->enum('default_type', ['high', 'medium', 'low', 'urgent']); // Default priority
            $table->enum('default_status', ['unassigned', 'open', 'waiting_on_customer','waiting_on_third_party','resolved','closed']); // Default status (you can modify values based on your needs)
            $table->string('imap_host'); // IMAP host
            $table->integer('imap_port'); // IMAP port
            $table->string('imap_user'); // IMAP user
            $table->string('imap_password'); // IMAP password
            $table->string('sender_name'); // Sender's name
            $table->string('sender_email'); // Sender's email address
            $table->enum('assign_method', ['random', 'round_robin']); // Assign method (random or round robin)
            $table->text('signature')->nullable(); // Signature (can be nullable if not always required)
            $table->enum('is_delete', ['Yes', 'No'])->default('No');
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
