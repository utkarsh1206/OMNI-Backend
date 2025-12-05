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
        Schema::create('ecdial_state_call_times', function (Blueprint $table) {
            $table->string('state_call_time_id', 10)->primary();
            $table->string('state_call_time_state', 2);
            $table->string('state_call_time_name', 30);
            $table->string('state_call_time_comments', 255)->default('');

            $table->unsignedSmallInteger('sct_default_start')->default(900);
            $table->unsignedSmallInteger('sct_default_stop')->default(2100);

            $table->unsignedSmallInteger('sct_sunday_start')->default(0);
            $table->unsignedSmallInteger('sct_sunday_stop')->default(0);
            $table->unsignedSmallInteger('sct_monday_start')->default(0);
            $table->unsignedSmallInteger('sct_monday_stop')->default(0);
            $table->unsignedSmallInteger('sct_tuesday_start')->default(0);
            $table->unsignedSmallInteger('sct_tuesday_stop')->default(0);
            $table->unsignedSmallInteger('sct_wednesday_start')->default(0);
            $table->unsignedSmallInteger('sct_wednesday_stop')->default(0);
            $table->unsignedSmallInteger('sct_thursday_start')->default(0);
            $table->unsignedSmallInteger('sct_thursday_stop')->default(0);
            $table->unsignedSmallInteger('sct_friday_start')->default(0);
            $table->unsignedSmallInteger('sct_friday_stop')->default(0);
            $table->unsignedSmallInteger('sct_saturday_start')->default(0);
            $table->unsignedSmallInteger('sct_saturday_stop')->default(0);
        });

        $defaultFields = [
            'state_call_time_comments' => '',
            'sct_default_start' => 900,
            'sct_default_stop' => 2100,
            'sct_sunday_start' => 0,
            'sct_sunday_stop' => 0,
            'sct_monday_start' => 0,
            'sct_monday_stop' => 0,
            'sct_tuesday_start' => 0,
            'sct_tuesday_stop' => 0,
            'sct_wednesday_start' => 0,
            'sct_wednesday_stop' => 0,
            'sct_thursday_start' => 0,
            'sct_thursday_stop' => 0,
            'sct_friday_start' => 0,
            'sct_friday_stop' => 0,
            'sct_saturday_start' => 0,
            'sct_saturday_stop' => 0,
        ];

        $records = [
            ['state_call_time_id' => 'alabama', 'state_call_time_state' => 'AL', 'state_call_time_name' => 'Alabama 8am-8pm and Sunday', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2000, 'sct_sunday_start' => 2400, 'sct_sunday_stop' => 2400, 'sct_monday_start' => 0, 'sct_monday_stop' => 0, 'sct_tuesday_start' => 0, 'sct_tuesday_stop' => 0, 'sct_wednesday_start' => 0, 'sct_wednesday_stop' => 0, 'sct_thursday_start' => 0, 'sct_thursday_stop' => 0, 'sct_friday_start' => 0, 'sct_friday_stop' => 0, 'sct_saturday_start' => 0, 'sct_saturday_stop' => 0],
            ['state_call_time_id' => 'illinois', 'state_call_time_state' => 'IL', 'state_call_time_name' => 'Illinois 8am', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2100, 'sct_sunday_start' => 0, 'sct_sunday_stop' => 0, 'sct_monday_start' => 0, 'sct_monday_stop' => 0, 'sct_tuesday_start' => 0, 'sct_tuesday_stop' => 0, 'sct_wednesday_start' => 0, 'sct_wednesday_stop' => 0, 'sct_thursday_start' => 0, 'sct_thursday_stop' => 0, 'sct_friday_start' => 0, 'sct_friday_stop' => 0, 'sct_saturday_start' => 0, 'sct_saturday_stop' => 0],
            ['state_call_time_id' => 'indiana', 'state_call_time_state' => 'IN', 'state_call_time_name' => 'Indiana 8pm restriction', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2000],
            ['state_call_time_id' => 'kentucky', 'state_call_time_state' => 'KY', 'state_call_time_name' => 'Kentucky 10am restriction', 'state_call_time_comments' => '', 'sct_default_start' => 1000, 'sct_default_stop' => 2100],
            ['state_call_time_id' => 'louisiana', 'state_call_time_state' => 'LA', 'state_call_time_name' => 'Louisiana 8am-8pm and Sunday', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2000, 'sct_sunday_start' => 2400, 'sct_sunday_stop' => 2400],
            ['state_call_time_id' => 'massachuse', 'state_call_time_state' => 'MA', 'state_call_time_name' => 'Massachusetts 8am-8pm', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2000],
            ['state_call_time_id' => 'mississipp', 'state_call_time_state' => 'MS', 'state_call_time_name' => 'Mississippi 8am-8pm and Sunday', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2000, 'sct_sunday_start' => 2400, 'sct_sunday_stop' => 2400],
            ['state_call_time_id' => 'nebraska', 'state_call_time_state' => 'NE', 'state_call_time_name' => 'Nebraska 8am', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2100],
            ['state_call_time_id' => 'nevada', 'state_call_time_state' => 'NV', 'state_call_time_name' => 'Nevada 8pm restriction', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2000],
            ['state_call_time_id' => 'pennsylvan', 'state_call_time_state' => 'PA', 'state_call_time_name' => 'Pennsylvania sunday restrictio', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2100, 'sct_sunday_start' => 1330, 'sct_sunday_stop' => 2100],
            ['state_call_time_id' => 'rhodeislan', 'state_call_time_state' => 'RI', 'state_call_time_name' => 'Rhode Island restrictions', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 1800, 'sct_sunday_start' => 2400, 'sct_sunday_stop' => 2400, 'sct_saturday_start' => 1000, 'sct_saturday_stop' => 1700],
            ['state_call_time_id' => 'sdakota', 'state_call_time_state' => 'SD', 'state_call_time_name' => 'South Dakota sunday restrict', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2100, 'sct_sunday_start' => 2400, 'sct_sunday_stop' => 2400],
            ['state_call_time_id' => 'tennessee', 'state_call_time_state' => 'TN', 'state_call_time_name' => 'Tennessee 8am', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2100],
            ['state_call_time_id' => 'texas', 'state_call_time_state' => 'TX', 'state_call_time_name' => 'Texas sunday restriction', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2100, 'sct_sunday_start' => 1200, 'sct_sunday_stop' => 2100],
            ['state_call_time_id' => 'utah', 'state_call_time_state' => 'UT', 'state_call_time_name' => 'Utah 8pm restriction', 'state_call_time_comments' => '', 'sct_default_start' => 900, 'sct_default_stop' => 2000],
            ['state_call_time_id' => 'washington', 'state_call_time_state' => 'WA', 'state_call_time_name' => 'Washington 8am', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2100],
            ['state_call_time_id' => 'wyoming', 'state_call_time_state' => 'WY', 'state_call_time_name' => 'Wyoming 8am-8pm', 'state_call_time_comments' => '', 'sct_default_start' => 800, 'sct_default_stop' => 2000],
        ];

        foreach ($records as $row) {
            DB::table('ecdial_state_call_times')->insert(array_merge($defaultFields, $row));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecdial_state_call_times');
    }
};
