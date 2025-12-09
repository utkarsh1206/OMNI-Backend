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
        Schema::create('ecdial_list', function (Blueprint $table) {
            $table->increments('lead_id');

            $table->dateTime('entry_date')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('modify_date')->useCurrent()->useCurrentOnUpdate();
            $table->string('status', 12)->nullable();
            $table->enum('new_lead_on_incoming_call', ['N','Y'])->default('N');
            $table->string('user', 320)->nullable();
            $table->string('vendor_lead_code', 20)->nullable();
            $table->string('alt_dial', 20)->default('NONE');
            $table->string('source_id', 50)->nullable();
            $table->unsignedBigInteger('list_id')->default(0);
            $table->decimal('gmt_offset_now', 4, 2)->default(0.00);

            $table->enum('called_since_last_reset', [
                'Y','N','Y1','Y2','Y3','Y4','Y5','Y6','Y7','Y8','Y9','Y10'
            ])->default('N');

            $table->string('phone_code', 10)->nullable();
            $table->string('phone_number', 100)->nullable();
            // $table->string('title', 4)->nullable();
            $table->string('first_name', 30)->nullable();
            // $table->string('middle_initial', 2)->nullable();
            $table->string('last_name', 30)->nullable();
            $table->string('address1', 100)->nullable();
            $table->string('address2', 100)->nullable();
            $table->string('address3', 100)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 4)->nullable();
            // $table->string('province', 50)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country_code', 3)->nullable();
            // $table->enum('gender', ['M','F','U'])->default('U');
            // $table->date('date_of_birth')->nullable();
            $table->string('alt_phone', 100)->nullable();
            $table->string('email', 70)->nullable();
            // $table->string('security_phrase', 100)->nullable();
            $table->string('comments', 1000)->nullable();
            $table->unsignedSmallInteger('called_count')->default(0);
            $table->dateTime('last_local_call_time')->nullable();
            $table->smallInteger('rank')->default(0);
            $table->string('owner', 20)->default('');
            $table->unsignedBigInteger('entry_list_id')->default(0);
            $table->smallInteger('per_day_maximum_callback_hops_for_fail_calls')->default(0);
            $table->smallInteger('per_day_maximum_callback_hops_for_success_calls')->default(0);
            $table->smallInteger('global_maximum_callback_hops_for_fail_calls')->default(0);
            $table->smallInteger('global_maximum_callback_hops_for_success_calls')->default(0);
            // $table->string('area', 100)->nullable();
            // $table->string('reference_id', 255)->nullable();
            // $table->string('contact_person', 30)->nullable();
            $table->dateTime('entrydate')->nullable();
            $table->dateTime('lastdate')->nullable();
            $table->string('region', 30)->nullable();
            $table->unsignedTinyInteger('lead_priority')->default(0);
            $table->string('customer_dispo_status', 12)->nullable();
            $table->timestamp('last_dialable_time')->useCurrent();

            // Indexes
            $table->index(['phone_number', 'list_id'], 'phone_list');
            $table->index(['list_id', 'status'], 'list_status');
            $table->index(['called_since_last_reset', 'status', 'list_id', 'gmt_offset_now', 'modify_date'], 'load_Cindex_list');
            // $table->index(['reference_id', 'list_id'], 'reference_list');
            $table->index(['phone_number', 'modify_date'], 'phone_modify');
            $table->index(['vendor_lead_code', 'modify_date'], 'vendor_mod');
            $table->index(['status', 'user', 'modify_date'], 'status_user_mod');
            $table->index(['entry_date', 'list_id'], 'entrydate_listid');
            $table->index(['lead_id', 'list_id'], 'Eindex_list');
            $table->index(['phone_number', 'lead_id'], 'Bindex_list');
            $table->index('list_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_list');
    }
};
