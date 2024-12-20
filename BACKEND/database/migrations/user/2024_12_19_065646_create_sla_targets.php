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
        Schema::create('sla_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_id')->constrained('sla_settings')->onDelete('cascade');
            $table->enum('priority_type', ['Critical', 'High', 'Medium', 'Low']);
            $table->integer('response_time_value');
            $table->enum('response_time_unit', ['Minute', 'Hour']);
            $table->integer('alert_before_response_value');
            $table->enum('alert_before_response_unit', ['Minute', 'Hour']);
            $table->integer('resolve_time_value');
            $table->enum('resolve_time_unit', ['Minute', 'Hour']);
            $table->integer('alert_before_resolve_value');
            $table->enum('alert_before_resolve_unit', ['Minute', 'Hour']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('sla_targets');
    }
};
