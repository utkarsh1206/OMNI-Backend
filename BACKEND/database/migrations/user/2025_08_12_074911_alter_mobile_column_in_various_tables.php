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
            if (Schema::hasColumn('accounts', 'mobile')) {
                $table->string('mobile', 20)->nullable()->change();
            }
        });
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'mobile')) {
                $table->string('mobile', 20)->nullable()->change();
            }
        });
        Schema::table('live_call_logs', function (Blueprint $table) {
            if (Schema::hasColumn('live_call_logs', 'mobile_number')) {
                $table->string('mobile_number', 20)->change();
            }
        });
        Schema::table('live_calls', function (Blueprint $table) {
            if (Schema::hasColumn('live_calls', 'mobile_number')) {
                $table->string('mobile_number', 20)->change();
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile', 20)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->bigInteger('mobile')->nullable()->unsigned()->change();
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->bigInteger('mobile')->nullable()->unsigned()->change();
        });
        Schema::table('live_call_logs', function (Blueprint $table) {
            $table->string('mobile_number', 15)->change();
        });
        Schema::table('live_calls', function (Blueprint $table) {
            $table->string('mobile_number', 15)->change();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('mobile')->nullable()->unsigned()->change();
        });
    }
};
