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
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'mobile')) {
                $table->string('mobile', 20)->change();
            }
        });
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'mobile')) {
                $table->string('mobile', 20)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->bigInteger('mobile')->unsigned()->change();
        });
        Schema::table('tenants', function (Blueprint $table) {
            $table->bigInteger('mobile')->unsigned()->change();
        });
    }
};
