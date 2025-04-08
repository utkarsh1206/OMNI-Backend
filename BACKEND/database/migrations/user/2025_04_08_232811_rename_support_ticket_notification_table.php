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
        Schema::rename('support_ticket_notification', 'support_ticket_notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('support_ticket_notifications', 'support_ticket_notification');
    }
};
