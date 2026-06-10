<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds API-driven options to the ecdial_lists_fields table.
     *
     * New columns:
     *  - uses_api      : Whether this field fetches its options from an api_configs entry.
     *  - api_config_id : FK to api_configs.id — which API to call.
     *
     * Multi-parameter dependencies are stored in ecdial_list_field_dependencies (separate migration).
     */
    public function up(): void
    {
        if (Schema::hasColumn('ecdial_lists_fields', 'uses_api')) {
            return;
        }

        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->enum('uses_api', ['Y', 'N'])->default('N')->after('default_value');

            $table->unsignedBigInteger('api_config_id')
                ->nullable()
                ->after('uses_api');

            $table->foreign('api_config_id')
                ->references('id')
                ->on('api_configs')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->dropForeign(['api_config_id']);
            $table->dropColumn(['uses_api', 'api_config_id']);
        });
    }
};
