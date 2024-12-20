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
        Schema::create('sla_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_id')->constrained('sla_settings')->onDelete('cascade');
            $table->date('holiday_date');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('sla_holidays');
    }
};
