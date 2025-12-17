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
        Schema::table('lead_import_histories', function (Blueprint $table) {
            $table->dropForeign('lead_import_histories_user_id_foreign');
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('external_token', 255)->nullable()->after('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_import_histories', function (Blueprint $table) {
            $table->dropForeign('lead_import_histories_user_id_foreign');
            $table->dropColumn('external_token');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
