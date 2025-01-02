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
        Schema::table('support_ticket_notification', function (Blueprint $table) {
            $table->integer('created_by')->after("support_ticket_id")->nullable();
            $table->string('icon')->after("created_by")->nullable();
            $table->string('color')->after("icon")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_notification', function (Blueprint $table) {
            $table->dropColumn(["created_by","icon"]);
        });
    }
};
