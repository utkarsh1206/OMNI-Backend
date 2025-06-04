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
        Schema::create('custom_cdr_lead', function (Blueprint $table) {
            $table->id();
            $table->string('call_unique_id')->nullable();
            $table->unsignedBigInteger('list_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->json('lead_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_cdr_lead');
    }
};
