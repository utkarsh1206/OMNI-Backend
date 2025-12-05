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
        Schema::create('ecdial_call_times', function (Blueprint $table) {
            $table->string('call_time_id', 10)->primary();
            $table->string('call_time_name', 30);
            $table->string('call_time_comments', 255)->default('');

            $table->unsignedSmallInteger('ct_default_start')->default(900);
            $table->unsignedSmallInteger('ct_default_stop')->default(2100);

            $table->unsignedSmallInteger('ct_sunday_start')->default(0);
            $table->unsignedSmallInteger('ct_sunday_stop')->default(0);

            $table->unsignedSmallInteger('ct_monday_start')->default(0);
            $table->unsignedSmallInteger('ct_monday_stop')->default(0);

            $table->unsignedSmallInteger('ct_tuesday_start')->default(0);
            $table->unsignedSmallInteger('ct_tuesday_stop')->default(0);

            $table->unsignedSmallInteger('ct_wednesday_start')->default(0);
            $table->unsignedSmallInteger('ct_wednesday_stop')->default(0);

            $table->unsignedSmallInteger('ct_thursday_start')->default(0);
            $table->unsignedSmallInteger('ct_thursday_stop')->default(0);

            $table->unsignedSmallInteger('ct_friday_start')->default(0);
            $table->unsignedSmallInteger('ct_friday_stop')->default(0);

            $table->unsignedSmallInteger('ct_saturday_start')->default(0);
            $table->unsignedSmallInteger('ct_saturday_stop')->default(0);

            $table->text('ct_state_call_times')->nullable();

            $table->string('default_afterhours_filename_override', 255)->default('');
            $table->string('sunday_afterhours_filename_override', 255)->default('');
            $table->string('monday_afterhours_filename_override', 255)->default('');
            $table->string('tuesday_afterhours_filename_override', 255)->default('');
            $table->string('wednesday_afterhours_filename_override', 255)->default('');
            $table->string('thursday_afterhours_filename_override', 255)->default('');
            $table->string('friday_afterhours_filename_override', 255)->default('');
            $table->string('saturday_afterhours_filename_override', 255)->default('');

            $table->index('call_time_id');
        });

        // -----------------------------------------------------
        // INSERT DEFAULT CALL TIME DATA HERE
        // -----------------------------------------------------

        $defaultFields = [
            'ct_sunday_start' => 0,
            'ct_sunday_stop' => 0,
            'ct_monday_start' => 0,
            'ct_monday_stop' => 0,
            'ct_tuesday_start' => 0,
            'ct_tuesday_stop' => 0,
            'ct_wednesday_start' => 0,
            'ct_wednesday_stop' => 0,
            'ct_thursday_start' => 0,
            'ct_thursday_stop' => 0,
            'ct_friday_start' => 0,
            'ct_friday_stop' => 0,
            'ct_saturday_start' => 0,
            'ct_saturday_stop' => 0,
            'default_afterhours_filename_override' => '',
            'sunday_afterhours_filename_override' => '',
            'monday_afterhours_filename_override' => '',
            'tuesday_afterhours_filename_override' => '',
            'wednesday_afterhours_filename_override' => '',
            'thursday_afterhours_filename_override' => '',
            'friday_afterhours_filename_override' => '',
            'saturday_afterhours_filename_override' => '',
        ];

        $callTimes = [
            [
                'call_time_id' => '12pm-5pm',
                'call_time_name' => 'default 12pm to 5pm calling',
                'call_time_comments' => '',
                'ct_default_start' => 1200,
                'ct_default_stop' => 1700,
                'ct_state_call_times' => '|alabama|',
            ],
            [
                'call_time_id' => '12pm-9pm',
                'call_time_name' => 'default 12pm to 9pm calling',
                'call_time_comments' => '',
                'ct_default_start' => 1200,
                'ct_default_stop' => 1200,
            ],
            [
                'call_time_id' => '24hours',
                'call_time_name' => 'default 24 hours calling',
                'call_time_comments' => '',
                'ct_default_start' => 0,
                'ct_default_stop' => 2400,
            ],
            [
                'call_time_id' => '5pm-9pm',
                'call_time_name' => 'default 5pm to 9pm calling',
                'call_time_comments' => '',
                'ct_default_start' => 1700,
                'ct_default_stop' => 2100,
            ],
            [
                'call_time_id' => '9am-5pm',
                'call_time_name' => 'default 9am to 5pm calling',
                'call_time_comments' => '',
                'ct_default_start' => 900,
                'ct_default_stop' => 1700,
            ],
        ];

        foreach ($callTimes as $row) {
            DB::table('ecdial_call_times')->insert(array_merge($defaultFields, $row));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_call_times');
    }
};
