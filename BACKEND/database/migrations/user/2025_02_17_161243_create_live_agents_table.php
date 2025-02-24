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
        Schema::create('live_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('extension')->nullable();
            $table->string('status')->nullable();
            $table->string('customer')->nullable();
            // $table->foreignId('campaign')->constrained('campaigns')->onDelete('cascade');
            $table->integer('campaign_id')->nullable();
            // $table->foreignId('login_ingroup')->constrained('login_ingroups')->onDelete('cascade');
            $table->integer('ingroup_id')->nullable();
            $table->timestamp('status_last_updated_at')->comment('Value in seconds')->nullable();
            $table->integer('total_calls')->nullable();
            $table->timestamp('login_time')->nullable();
            $table->timestamp('ready_hr')->nullable();
            $table->timestamp('talk_hr')->nullable();
            $table->integer('talk_avg')->comment('Value in seconds')->nullable();
            // $table->foreignId('ingroup')->constrained('ingroups')->onDelete('cascade');
            $table->integer('ingroup')->nullable();
            $table->integer('lagged_duration')->comment('Value in seconds')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_agents');
    }
};
