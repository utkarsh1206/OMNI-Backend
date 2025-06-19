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
            $table->unsignedBigInteger('default_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            $table->enum('default_status', [
                'unassigned',
                'open',
                'waiting_on_customer',
                'waiting_on_third_party',
                'resolved',
                'closed'
            ])->default('unassigned')->change();
        });
    }
};
