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
        Schema::table('sla_targets', function (Blueprint $table) {
            $table->dropColumn('priority_type');
        });

        Schema::table('sla_targets', function (Blueprint $table) {
            $table->bigInteger('priority_type')->nullable()->after('escalation_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sla_targets', function (Blueprint $table) {
            $table->dropColumn('priority_type');
        });

        Schema::table('sla_targets', function (Blueprint $table) {
            $table->enum('priority_type', ['low', 'medium', 'high'])->default('medium');
        });
    }
};
