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
        Schema::create('phones', function (Blueprint $table) {
            $table->string('extension', 100)->nullable();
            $table->string('dialplan_number', 20)->nullable();
            $table->string('voicemail_id', 10)->nullable();
            $table->string('phone_ip', 15)->nullable();
            $table->string('computer_ip', 15)->nullable();
            $table->string('server_ip', 15)->nullable();
            $table->string('login', 320)->nullable();
            $table->string('pass', 320)->nullable();
            $table->string('status', 10)->nullable();
            $table->enum('active', ['Y', 'N'])->nullable();
            $table->string('phone_type', 50)->nullable();
            $table->string('fullname', 50)->nullable();
            $table->string('company', 10)->nullable();
            $table->string('picture', 19)->nullable();
            $table->integer('messages')->nullable()->default(0);
            $table->integer('old_messages')->nullable()->default(0);
            $table->enum('protocol', ['SIP', 'Zap', 'IAX2', 'EXTERNAL'])->default('SIP');
            $table->string('local_gmt', 6)->default('-5');
            $table->string('ASTmgrUSERNAME', 20)->default('cron');
            $table->string('ASTmgrSECRET', 20)->default('1234');
            $table->string('login_user', 20)->nullable();
            $table->string('login_pass', 20)->nullable();
            $table->string('login_campaign', 10)->nullable();
            $table->string('park_on_extension', 10)->default('8301');
            $table->string('conf_on_extension', 10)->default('8302');
            $table->string('ecdial_park_on_extension', 10)->default('8301');
            $table->string('ecdial_park_on_filename', 10)->default('park');
            $table->string('monitor_prefix', 10)->default('8612');
            $table->string('recording_exten', 10)->default('8309');
            $table->string('voicemail_exten', 10)->default('8501');
            $table->string('voicemail_dump_exten', 20)->default('85026666666666');
            $table->string('ext_context', 20)->default('default');
            $table->string('dtmf_send_extension', 100)->default('local/8500998@default');
            $table->string('call_out_number_group', 100)->default('Zap/g2/');
            $table->string('client_browser', 100)->default('/usr/bin/mozilla');
            $table->string('install_directory', 100)->default('/usr/local/perl_TK');
            $table->string('local_web_callerID_URL', 255)->default('http://astguiclient.sf.net/test_callerid_output.php');
            $table->string('ecdial_web_URL', 255)->default('http://astguiclient.sf.net/test_ecdial_output.php');
            $table->enum('AGI_call_logging_enabled', ['0', '1'])->default('1');
            $table->enum('user_switching_enabled', ['0', '1'])->default('1');
            $table->enum('conferencing_enabled', ['0', '1'])->default('1');
            $table->enum('admin_hangup_enabled', ['0', '1'])->default('0');
            $table->enum('admin_hijack_enabled', ['0', '1'])->default('0');
            $table->enum('admin_monitor_enabled', ['0', '1'])->default('1');
            $table->enum('call_parking_enabled', ['0', '1'])->default('1');
            $table->enum('updater_check_enabled', ['0', '1'])->default('1');
            $table->enum('AFLogging_enabled', ['0', '1'])->default('1');
            $table->enum('QUEUE_ACTION_enabled', ['0', '1'])->default('1');
            $table->enum('CallerID_popup_enabled', ['0', '1'])->default('1');
            $table->enum('voicemail_button_enabled', ['0', '1'])->default('1');
            $table->enum('enable_fast_refresh', ['0', '1'])->default('0');
            $table->integer('fast_refresh_rate')->default(1000);
            $table->enum('enable_persistant_mysql', ['0', '1'])->default('0');
            $table->enum('auto_dial_next_number', ['0', '1'])->default('1');
            $table->enum('VDstop_rec_after_each_call', ['0', '1'])->default('1');
            $table->string('DBX_server', 15)->nullable();
            $table->string('DBX_database', 15)->default('asterisk');
            $table->string('DBX_user', 15)->default('cron');
            $table->string('DBX_pass', 15)->default('1234');
            $table->integer('DBX_port')->default(3306);
            $table->string('DBY_server', 15)->nullable();
            $table->string('DBY_database', 15)->default('asterisk');
            $table->string('DBY_user', 15)->default('cron');
            $table->string('DBY_pass', 15)->default('1234');
            $table->integer('DBY_port')->default(3306);
            $table->string('outbound_cid', 20)->nullable();
            $table->enum('enable_sipsak_messages', ['0', '1'])->default('0');
            $table->string('email', 100)->nullable();
            $table->string('template_id', 15)->nullable();
            $table->text('conf_override')->nullable();
            $table->string('phone_context', 20)->default('default');
            $table->smallInteger('phone_ring_timeout')->default(60);
            $table->string('conf_secret', 20)->default('test');
            $table->enum('delete_vm_after_email', ['N', 'Y'])->default('N');
            $table->enum('is_webphone', ['Y', 'N'])->default('N');
            $table->enum('use_external_server_ip', ['Y', 'N'])->default('N');
            $table->string('codecs_list', 100)->default('');
            $table->enum('codecs_with_template', ['0', '1'])->default('0');
            $table->enum('webphone_dialpad', ['Y', 'N', 'TOGGLE', 'TOGGLE_OFF'])->default('Y');
            $table->enum('on_hook_agent', ['Y', 'N'])->default('N');
            $table->timestamps(0); // for created_at and updated_at timestamps
        });

        // Adding indexes
        Schema::table('phones', function (Blueprint $table) {
            $table->unique(['extension', 'server_ip'], 'extenserver');
            $table->index('server_ip');
            $table->index('extension');
            $table->index('active');
            $table->index(['login', 'pass', 'active'], 'login_pass_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('phones');
    }
};
