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
        Schema::rename('sms_provider_configurations', 'api_configurations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('api_configurations', 'sms_provider_configurations');
    }
};
