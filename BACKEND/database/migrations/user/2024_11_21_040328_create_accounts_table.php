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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); // automatically adds an auto-incrementing id
            $table->integer('user_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('territory_id')->nullable();
            $table->integer('timezone_id')->nullable();
            $table->integer('industry_id')->nullable();
            $table->integer('business_type_id')->nullable();
            $table->integer('no_of_employees')->nullable();
            $table->integer('lead_source_id')->nullable();
            $table->integer('contact_status_id')->nullable();
            $table->integer('product_id');
            $table->integer('campaign_id');
            $table->integer('contact_id')->nullable();
            $table->string('service_id', 50)->nullable();
            $table->string('property_id', 50)->nullable();
            $table->string('requirement', 100)->nullable();
            $table->string('edit_id', 155)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->float('annual_revenue')->nullable();
            $table->string('work', 100)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('department_name', 50)->nullable();
            $table->enum('do_not_disturb', ['No', 'Yes'])->default('No');
            $table->enum('has_authority', ['No', 'Yes'])->default('No');
            $table->text('address')->nullable();
            $table->integer('pincode')->nullable();
            $table->string('medium', 255)->nullable();
            $table->string('keyword', 255)->nullable();
            $table->string('tax_number', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('last_contacted_mode', 50)->nullable();
            $table->dateTime('last_contacted_time')->nullable();
            $table->dateTime('last_contacted_via_chat')->nullable();
            $table->string('last_activity_type', 20)->nullable();
            $table->dateTime('last_activity_date')->nullable();
            $table->string('active_sales_sequences', 10)->nullable();
            $table->string('completed_sales_sequences', 10)->nullable();
            $table->string('last_seen', 100)->nullable();
            $table->text('recent_note')->nullable();
            $table->dateTime('created_on')->nullable();
            $table->integer('updated_by')->nullable();
            $table->dateTime('updated_on')->nullable();
            $table->text('web_forms')->nullable();
            $table->string('email_status', 20)->nullable();
            $table->dateTime('last_assigned_on')->nullable();
            $table->string('activate_from_date', 150)->default('0000-00-00 00:00:00')->comment('Activate From Date');
            $table->string('activate_to_date', 150)->default('0000-00-00 00:00:00')->comment('Activate To Date');
            $table->integer('number_of_users')->default(0)->comment('Number Of Users');
            $table->string('account_unique_id', 255)->nullable()->comment('Account Unique ID');
            $table->string('validity_days', 50)->default('0')->comment('Validity Days');
            $table->string('product_deployed_type', 50)->nullable();
            $table->enum('neox_integration', ['Yes', 'No'])->default('Yes');
            $table->text('neox_integration_details')->nullable();
            $table->enum('phone_type', ['IP Phone', 'Analog Phone', 'Both'])->nullable();
            $table->text('phone_type_comment')->nullable();
            $table->enum('pstn_line', ['PRI', 'SIP', 'Both'])->nullable();
            $table->string('pstn_ports_qty', 100)->nullable();
            $table->text('isp_details')->nullable();
            $table->text('did_number_details')->nullable();
            $table->text('pilot_number_detail')->nullable();
            $table->text('extension_range_detail')->nullable();
            $table->string('server_serialnumber', 150)->nullable();
            $table->string('server_ip', 150)->nullable();
            $table->string('server_mac', 150)->nullable();
            $table->string('license_key', 150)->nullable();
            $table->string('secret_key', 150)->nullable();
            $table->string('no_of_users', 150)->nullable();
            $table->string('account_expiry_date', 150)->nullable();
            $table->string('last_license_update', 150)->nullable();
            $table->text('license_detail')->nullable();
            $table->string('company_name', 255);
            $table->string('contact_person_first_name', 255);
            $table->string('contact_person_last_name', 255);
            $table->string('contact_no', 255);
            $table->string('landline_no', 100)->nullable();
            $table->string('company_email', 255);
            $table->enum('is_delete', ['No', 'Yes'])->default('No');
            $table->timestamps(0); // created_at and updated_at

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('accounts');
    }
};
