<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 255);
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('password', 255);
            $table->string('email', 255);
            $table->bigInteger('mobile');
            $table->string('domain_name', 255);
            $table->string('company_code', 255);
            $table->string('company_name', 255);
            $table->string('server_serialnumber', 255)->nullable();
            $table->string('server_ip', 255)->nullable();
            $table->string('server_mac', 255)->nullable();
            $table->string('domain_url', 255)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('is_delete', ['Yes', 'No'])->default('No');
            $table->string('license_key')->nullable();
            $table->timestamp('license_updated_date')->default(DB::raw('CURRENT_TIMESTAMP'))->nullable();
            $table->timestamps();
            /*
            $table->text('address');
            $table->integer('zipcode')->unsigned();
            $table->string('email', 255);
            $table->bigInteger('phone_number');
            $table->string('gstin', 255);
            $table->string('cin', 255);
            $table->string('service_tax_number', 255)->nullable();
            $table->integer('cgst')->unsigned();
            $table->integer('sgst')->unsigned();
            $table->integer('igst')->unsigned();
            $table->integer('other_tax')->unsigned();
            $table->longText('quotation_term_cond');
            $table->longText('invoice_term_cond');
            $table->longText('proforma_term_cond');
            $table->string('payment_of_favour', 255);
            $table->string('logo', 255);
            $table->string('pan_number', 255)->nullable();
            $table->string('website', 255);
            $table->string('product_code', 255)->nullable();
            $table->string('subscription_type', 255);
            $table->string('plan_id', 255)->nullable();
            $table->string('plan_name', 255)->nullable();
            $table->string('no_of_users', 255);
            $table->date('valid_from');
            $table->date('valid_to');
            $table->date('additional_end_date');
            $table->integer('additional_days')->unsigned();
            $table->integer('max_email_alloted')->unsigned();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->enum('api_key_activate', ['No', 'Yes'])->default('No');
            $table->enum('gst_applicable', ['No', 'Yes'])->default('No');
            $table->string('renewal_type', 255);
            $table->enum('is_verified', ['No', 'Yes'])->default('No');
            $table->enum('system_mails', ['No', 'Yes'])->default('Yes');
            $table->enum('product_deployed_type', ['IP-PBX', 'Dial Center', 'Both'])->nullable();
            $table->enum('neox_integration', ['Yes', 'No'])->nullable();
            $table->text('neox_integration_details')->nullable();
            $table->enum('phone_type', ['IP Phone', 'Analog Phone', 'Both'])->nullable();
            $table->text('phone_type_comment')->nullable();
            $table->enum('pstn_line', ['PRI', 'SIP', 'Both'])->nullable();
            $table->string('pstn_ports_qty', 10)->nullable();
            $table->text('isp_details')->nullable();
            $table->text('did_number_details')->nullable();
            $table->text('pilot_number_detail')->nullable();
            $table->text('extension_range_detail')->nullable();
            $table->date('account_expiry_date')->nullable();
            $table->string('server_serialnumber', 255)->nullable();
            $table->string('server_ip', 255)->nullable();
            $table->string('server_mac', 255)->nullable();
            $table->string('customer_id', 255)->nullable();
            $table->string('domain_url', 255)->nullable();
            $table->string('license_key', 255)->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->text('license_detail')->nullable();
            $table->date('last_license_update')->nullable();
            $table->dateTime('created_on');
            $table->dateTime('updated_on');
            $table->dateTime('verified_on');
            $table->enum('is_delete', ['No', 'Yes'])->default('No');
            $table->text('neox_token')->nullable();
            $table->string('neox_local_ip', 225)->nullable();
            $table->dateTime('created');
            $table->dateTime('modified');

            $table->timestamps();
*/
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
