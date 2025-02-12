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
        Schema::table('email_configures', function (Blueprint $table) {
            //
            $table->enum('default_type', ['Problems', 'Help', 'Incident', 'Service Request', 'Service Delivery', 'Service Assurance', 'Questions', 'Feature Request', 'Other', 'Change Management'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            //
        });
    }
};
