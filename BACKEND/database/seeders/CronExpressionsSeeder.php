<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CronExpression;

class CronExpressionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cronExpressions = [
            ['schedule_type' => 'every minute', 'schedule_expression' => '* * * * *'],
            ['schedule_type' => 'every 1 minute', 'schedule_expression' => '* * * * *'],
            ['schedule_type' => 'every 2 minutes', 'schedule_expression' => '*/2 * * * *'],
            ['schedule_type' => 'every even minute', 'schedule_expression' => '*/2 * * * *'],
            ['schedule_type' => 'every uneven minute', 'schedule_expression' => '*/2 * * * *'],
            ['schedule_type' => 'every 3 minutes', 'schedule_expression' => '*/3 * * * *'],
            ['schedule_type' => 'every 4 minutes', 'schedule_expression' => '*/4 * * * *'],
            ['schedule_type' => 'every 5 minutes', 'schedule_expression' => '*/5 * * * *'],
            ['schedule_type' => 'every five minutes', 'schedule_expression' => '*/5 * * * *'],
            ['schedule_type' => 'every 6 minutes', 'schedule_expression' => '*/6 * * * *'],
            ['schedule_type' => 'every 10 minutes', 'schedule_expression' => '*/10 * * * *'],
            ['schedule_type' => 'every 15 minutes', 'schedule_expression' => '*/15 * * * *'],
            ['schedule_type' => 'every fifteen minutes', 'schedule_expression' => '*/15 * * * *'],
            ['schedule_type' => 'every ten minutes', 'schedule_expression' => '*/10 * * * *'],
            ['schedule_type' => 'every quarter hour', 'schedule_expression' => '*/15 * * * *'],
            ['schedule_type' => 'every 20 minutes', 'schedule_expression' => '*/20 * * * *'],
            ['schedule_type' => 'every 30 minutes', 'schedule_expression' => '*/30 * * * *'],
            ['schedule_type' => 'every hour at 30 minutes', 'schedule_expression' => '30 * * * *'],
            ['schedule_type' => 'every half hour', 'schedule_expression' => '30 * * * *'],
            ['schedule_type' => 'every 60 minutes', 'schedule_expression' => '0 * * * *'],
            ['schedule_type' => 'every hour', 'schedule_expression' => '0 * * * *'],
            ['schedule_type' => 'every 1 hour', 'schedule_expression' => '0 * * * *'],
            ['schedule_type' => 'every 2 hours', 'schedule_expression' => '0 */2 * * *'],
            ['schedule_type' => 'every two hours', 'schedule_expression' => '0 */2 * * *'],
            ['schedule_type' => 'every even hour', 'schedule_expression' => '0 */2 * * *'],
            ['schedule_type' => 'every other hour', 'schedule_expression' => '0 */2 * * *'],
            ['schedule_type' => 'every 3 hours', 'schedule_expression' => '0 */3 * * *'],
            ['schedule_type' => 'every three hours', 'schedule_expression' => '0 */3 * * *'],
            ['schedule_type' => 'every 4 hours', 'schedule_expression' => '0 */4 * * *'],
            ['schedule_type' => 'every 6 hours', 'schedule_expression' => '0 */6 * * *'],
            ['schedule_type' => 'every six hours', 'schedule_expression' => '0 */6 * * *'],
            ['schedule_type' => 'every 8 hours', 'schedule_expression' => '0 */8 * * *'],
            ['schedule_type' => 'every 12 hours', 'schedule_expression' => '0 */12 * * *'],
            ['schedule_type' => 'hour range', 'schedule_expression' => '0 9-17 * * *'], 
            ['schedule_type' => 'between certain hours', 'schedule_expression' => '0 9-17 * * *'], // Example range for 9 AM to 5 PM
            ['schedule_type' => 'every day', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'daily', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'once a day', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'every night', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'every day at 1am', 'schedule_expression' => '0 1 * * *'],
            ['schedule_type' => 'every day at 2am', 'schedule_expression' => '0 2 * * *'],
            ['schedule_type' => 'every day 8am', 'schedule_expression' => '0 8 * * *'],
            ['schedule_type' => 'every morning', 'schedule_expression' => '0 6 * * *'],
            ['schedule_type' => 'every midnight', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'every day at midnight', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'every night at midnight', 'schedule_expression' => '0 0 * * *'],
            ['schedule_type' => 'every sunday', 'schedule_expression' => '0 0 * * SUN'],
            ['schedule_type' => 'every monday', 'schedule_expression' => '0 0 * * MON'],
            ['schedule_type' => 'every tuesday', 'schedule_expression' => '0 0 * * TUE'],
            ['schedule_type' => 'every wednesday', 'schedule_expression' => '0 0 * * WED'],
            ['schedule_type' => 'every thursday', 'schedule_expression' => '0 0 * * THU'],
            ['schedule_type' => 'every friday', 'schedule_expression' => '0 0 * * FRI'],
            ['schedule_type' => 'every friday at midnight', 'schedule_expression' => '0 0 * * FRI'],
            ['schedule_type' => 'every saturday', 'schedule_expression' => '0 0 * * SAT'],
            ['schedule_type' => 'every weekday', 'schedule_expression' => '0 0 * * MON-FRI'],
            ['schedule_type' => 'weekdays only', 'schedule_expression' => '0 0 * * MON-FRI'],
            ['schedule_type' => 'monday to friday', 'schedule_expression' => '0 0 * * MON-FRI'],
            ['schedule_type' => 'every weekend', 'schedule_expression' => '0 0 * * SAT,SUN'],
            ['schedule_type' => 'weekends only', 'schedule_expression' => '0 0 * * SAT,SUN'],
            ['schedule_type' => 'every 7 days', 'schedule_expression' => '0 0 */7 * *'],
            ['schedule_type' => 'every week', 'schedule_expression' => '0 0 * * SUN'],
            ['schedule_type' => 'weekly', 'schedule_expression' => '0 0 * * SUN'],
            ['schedule_type' => 'once a week', 'schedule_expression' => '0 0 * * SUN'],
            ['schedule_type' => 'every month', 'schedule_expression' => '0 0 1 * *'],
            ['schedule_type' => 'monthly', 'schedule_expression' => '0 0 1 * *'],
            ['schedule_type' => 'once a month', 'schedule_expression' => '0 0 1 * *'],
            ['schedule_type' => 'every other month', 'schedule_expression' => '0 0 1 */2 *'],
            ['schedule_type' => 'every quarter', 'schedule_expression' => '0 0 1 1,4,7,10 *'],
            ['schedule_type' => 'every 6 months', 'schedule_expression' => '0 0 1 */6 *'],
            ['schedule_type' => 'every year', 'schedule_expression' => '0 0 1 1 *'],
        ];
        foreach ($cronExpressions as $expression) {
            CronExpression::insert([
                'schedule_type' => $expression['schedule_type'],
                'schedule_expression' => $expression['schedule_expression'],
            ]);
        }

    }

}