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
        Schema::create('call_backs', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id');
            $table->integer('list_id');
            $table->integer('campaign_id')->nullable();
            $table->integer('user_id');
            $table->datetime('call_back_time');
            $table->enum('recipient', ['Useronly', 'Anyone'])->default('Useronly');
            $table->text('comments');
            $table->string('user_group', 255)->nullable();
            $table->string('lead_status', 255)->nullable()->default('CALLBK');
            $table->string('last_modified_user', 255)->nullable()->default('AGENT');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_backs');
    }
};
