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
        Schema::table('live_agents', function (Blueprint $table) {
            $table->string('uniqueid')->nullable()->after('rank');
            $table->timestamp('call_date')->nullable()->after('uniqueid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_agents', function (Blueprint $table) {
            $table->dropColumn('uniqueid');
            $table->dropColumn('call_date');
        });
    }
};
