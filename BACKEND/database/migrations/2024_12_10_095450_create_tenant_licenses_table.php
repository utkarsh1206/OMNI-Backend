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
        Schema::create('tenant_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->date('start_date');
            $table->date('expire_date');
            $table->integer('admin_ct')->default(0);
            $table->integer('agent_ct')->default(0);
            $table->integer('live_agent_ct')->default(0);
            $table->integer('manager_ct')->default(0);
            $table->integer('phone_ct')->default(0);
            $table->timestamps();

            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('tenant_licenses');
    }
};
