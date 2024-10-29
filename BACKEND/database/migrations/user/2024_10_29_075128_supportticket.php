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
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('inbound_group_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('assign_to_id');
            $table->string('default_type');
            $table->string('default_priority');
            $table->string('default_status');
            $table->string('subject');
            $table->string('departments');
            $table->string('to_emails');
            $table->longText('description');
            $table->string('attachment')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
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
