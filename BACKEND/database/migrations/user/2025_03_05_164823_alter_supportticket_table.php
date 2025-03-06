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
        Schema::table('supportticket', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable()->change();
            $table->dropColumn('departments');
            $table->unsignedBigInteger('department_id')->nullable()->after('account_id');
            
            // Define the foreign key constraint (assuming the referenced table is 'departments' and it has an 'id' column)
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supportticket', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable(false)->change();

            $table->string('departments')->nullable();

            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
