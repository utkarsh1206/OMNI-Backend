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
        Schema::table('api_configurations', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->integer('api_config_type_id')->after("description")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_configurations', function (Blueprint $table) {
            $table->dropColumn('api_config_type_id');
            $table->enum('type', ['Api', 'Sms','Chatbot'])->default('Sms')->after("description");
        });
    }
};
