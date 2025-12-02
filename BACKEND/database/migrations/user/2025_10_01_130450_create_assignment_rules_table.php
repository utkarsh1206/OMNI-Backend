<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignment_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('incident_type_id')->nullable()->constrained('incident_types')->cascadeOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('priorities')->cascadeOnDelete();
            $table->foreignId('sla_id')->nullable()->constrained('sla_settings')->cascadeOnDelete();
            $table->enum('method', [
                'round_robin',
                'least_loaded',
                'department_first',
                'priority_based',
                'hybrid',
                'live_agent_based',
                'random'
            ]);
            $table->json('parameters')->nullable();
            $table->boolean('enabled')->default(true);
            $table->integer('priority_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_rules');
    }
};
