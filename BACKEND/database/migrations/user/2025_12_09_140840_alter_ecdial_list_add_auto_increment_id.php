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
        Schema::table('ecdial_list', function (Blueprint $table) {
            // Drop primary key on lead_id first
            $table->dropPrimary('ecdial_list_lead_id_primary');

            // Change lead_id to non-auto-increment
            $table->unsignedBigInteger('lead_id')->change();

            // Add new auto-increment id as primary key
            $table->bigIncrements('id')->first();

            // Drop columns
            $table->dropColumn([
                'phone_code',
                'phone_number',
                'first_name',
                'last_name',
                'address1',
                'address2',
                'address3',
                'city',
                'state',
                'postal_code',
                'country_code',
                'alt_phone',
                'email',
                'comments'
            ]);

            // Drop indexes related to the dropped columns
            $table->dropIndex('phone_list'); // Index on ['phone_number', 'list_id']
            $table->dropIndex('phone_modify'); // Index on ['phone_number', 'modify_date']
            $table->dropIndex('Bindex_list'); // Index on ['phone_number', 'lead_id']
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecdial_list', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->increments('lead_id')->change();
            $table->primary('lead_id');

            // Add the columns back
            $table->string('phone_code', 10)->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->string('first_name', 30)->nullable();
            $table->string('last_name', 30)->nullable();
            $table->string('address1', 100)->nullable();
            $table->string('address2', 100)->nullable();
            $table->string('address3', 100)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 4)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country_code', 3)->nullable();
            $table->string('alt_phone', 100)->nullable();
            $table->string('email', 70)->nullable();
            $table->string('comments', 1000)->nullable();

            // Recreate indexes related to the columns
            $table->index(['phone_number', 'list_id'], 'phone_list');
            $table->index(['phone_number', 'modify_date'], 'phone_modify');
            $table->index(['phone_number', 'lead_id'], 'Bindex_list');
        });
    }
};
