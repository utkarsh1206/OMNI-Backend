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
        if (Schema::hasTable('custom_cdr_lead')) {
            Schema::rename('custom_cdr_lead', 'custom_cdr_leads');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('custom_cdr_leads')) {
            Schema::rename('custom_cdr_leads', 'custom_cdr_lead');
        }
    }
};
