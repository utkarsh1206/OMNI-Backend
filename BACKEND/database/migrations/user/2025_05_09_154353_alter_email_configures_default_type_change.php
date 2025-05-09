<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            // Drop the enum column
            $table->dropColumn('default_type');
        });

        Schema::table('email_configures', function (Blueprint $table) {
            // Add integer column with foreign key
            $table->unsignedBigInteger('default_type')->after('smtp_password');

            $table->foreign('default_type')
                ->references('id')
                ->on('ticket_types')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('email_configures', function (Blueprint $table) {
            // Drop foreign key and integer column
            $table->dropForeign(['default_type']);
            $table->dropColumn('default_type');
        });

        Schema::table('email_configures', function (Blueprint $table) {
            // Add back enum column (same as original)
            $table->enum('default_type', [
                'Problems', 'Help', 'Incident', 'Service Request',
                'Service Delivery', 'Service Assurance', 'Questions',
                'Feature Request', 'Other', 'Change Management'
            ])->after('smtp_password');
        });
    }
};
