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
        Schema::create('list_template_maps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('list_id');
            $table->unsignedBigInteger('template_id');
            $table->timestamps();

            $table->foreign('list_id')->references('list_id')->on('ecdial_lists')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('mail_template')->onDelete('cascade');
            $table->unique(['list_id', 'template_id']); // avoid duplicate mappings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_template_map');
    }
};
