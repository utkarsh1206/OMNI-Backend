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
        if (Schema::hasColumn('ecdial_lists_fields', 'dependency_mandatory')) {
            return;
        }

        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->enum('dependency_mandatory', ['Y', 'N'])
                ->default('N')
                ->after('api_config_id');

            $table->enum('response_type', ['array_values', 'array_objects', 'single_value'])
                ->default('array_values')->nullable()
                ->after('dependency_mandatory');

            $table->string('response_data_path', 255)
                ->nullable()
                ->after('response_type');

            $table->string('response_label_key', 255)
                ->nullable()
                ->after('response_data_path');

            $table->string('response_value_key', 255)
                ->nullable()
                ->after('response_label_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_lists_fields', function (Blueprint $table) {
            $table->dropColumn([
                'dependency_mandatory',
                'response_type',
                'response_data_path',
                'response_label_key',
                'response_value_key',
            ]);
        });
    }
};
