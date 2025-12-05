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
        Schema::create('ecdial_hopper', function (Blueprint $table) {
            $table->increments('hopper_id');
            $table->unsignedInteger('lead_id');
            $table->string('campaign_id', 8)->nullable();
            $table->enum('status', ['READY', 'QUEUE', 'INCALL', 'DONE', 'HOLD', 'DNC'])->default('READY');
            $table->string('user', 320)->nullable();
            $table->unsignedBigInteger('list_id');
            $table->decimal('gmt_offset_now', 4, 2)->default(0.00);
            $table->string('state', 2)->default('');
            $table->string('alt_dial', 6)->default('NONE');
            $table->tinyInteger('priority')->default(0);
            $table->string('source', 1)->default('');

            // Indexes
            $table->index('lead_id');
            $table->index(['campaign_id', 'status', 'user', 'list_id', 'priority', 'gmt_offset_now'], 'load_hopper');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_hopper');
    }
};
