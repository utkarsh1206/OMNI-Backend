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
        Schema::create('usergroup', function (Blueprint $table) {
            $table->id();
            //$table->unsignedBigInteger('tenant_id');
            $table->string('name', 255); 
            $table->string('campaign_id',255);
            $table->longText('description');
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
