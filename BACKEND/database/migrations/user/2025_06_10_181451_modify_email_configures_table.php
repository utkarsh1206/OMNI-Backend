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
        Schema::table('email_configures', function (Blueprint $table) {
            $table->string('smtp_user')->nullable()->change();
            $table->string('smtp_password')->nullable()->change();
            $table->string('imap_host')->nullable()->change();
            $table->integer('imap_port')->nullable()->change();
            $table->string('imap_user')->nullable()->change();
            $table->string('imap_password')->nullable()->change();
            $table->string('sender_name')->nullable()->change();
            $table->string('sender_email')->nullable()->change();
            $table->enum('assign_method', ['random', 'round_robin'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            $table->string('smtp_user')->nullable(false)->change();
            $table->string('smtp_password')->nullable(false)->change();
            $table->string('imap_host')->nullable(false)->change();
            $table->integer('imap_port')->nullable(false)->change();
            $table->string('imap_user')->nullable(false)->change();
            $table->string('imap_password')->nullable(false)->change();
            $table->string('sender_name')->nullable(false)->change();
            $table->string('sender_email')->nullable(false)->change();
            $table->enum('assign_method', ['random', 'round_robin'])->nullable(false)->change();
        });
    }
};
