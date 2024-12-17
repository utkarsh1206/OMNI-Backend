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
        Schema::create('supportticket', function (Blueprint $table) {
            $table->id();
            $table->string('support_ticket_id')->default(0);
            $table->unsignedBigInteger('product_id')->default(0);
            $table->unsignedBigInteger('assign_to_id')->default(0); 
            $table->unsignedBigInteger('campaign_id')->default(0);
            $table->unsignedBigInteger('inbound_group_id')->default(0);
            $table->unsignedBigInteger('account_id')->default(0);
            $table->unsignedBigInteger('contact_id')->default(0);
            $table->string('default_type');
            $table->string('default_priority');
            $table->string('default_status');
            $table->string('subject');
            $table->string('departments')->nullable();
            $table->string('to_emails');            
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->longText('description');
            $table->longText('attachment')->nullable();
            $table->enum('is_delete', ['Yes', 'No'])->default('No');
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
