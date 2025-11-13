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
        Schema::table('neox_configuration', function (Blueprint $table) {
            $table->string('sip_server_url', 255)->nullable()->after('neox_local_ip');
            $table->string('sip_ua_uri_domain', 255)->nullable()->after('sip_server_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('neox_configuration', function (Blueprint $table) {
            $table->dropColumn(['sip_server_url', 'sip_ua_uri_domain']);
        });
    }
};
