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
            $table->string('smtp_host')->nullable()->change();
            $table->integer('smtp_port')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            $table->string('smtp_host')->nullable(false)->change();
            $table->integer('smtp_port')->nullable(false)->change();
        });
    }
};
