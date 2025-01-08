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
        Schema::create('cron_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('command_signature');
            $table->string('schedule_type');            
            $table->string('schedule_expression'); 
            $table->string('number_of_day')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status of the cron job (active/inactive)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_configurations');
    }
};
