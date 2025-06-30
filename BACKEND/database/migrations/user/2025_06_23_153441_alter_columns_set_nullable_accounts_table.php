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
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('company_name')->nullable()->change();
            $table->string('contact_person_first_name')->nullable()->change();
            $table->string('contact_person_last_name')->nullable()->change();
            $table->string('contact_no')->nullable()->change();
            $table->string('company_email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('company_name')->nullable(false)->change();
            $table->string('contact_person_first_name')->nullable(false)->change();
            $table->string('contact_person_last_name')->nullable(false)->change();
            $table->string('contact_no')->nullable(false)->change();
            $table->string('company_email')->nullable(false)->change();
        });
    }
};
