<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SmsTemplate;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        SmsTemplate::truncate();

        SmsTemplate::create([
            'name' => 'Ticket Created',
            'content' => "Hi {#customer name#}, thanks for reaching out. The ticket is generated and ticket ID is {#ticket ##}.Sterlite Technologies Ltd.",
        ]);

        SmsTemplate::create([
            'name' => 'Ticket Resolved',
            'content' => 'Hi {#customer name#}, this is to confirm that your recent support ticket {#ticket ##} has been resolved and closed. We thank you for your patience.Sterlite Technologies Ltd.',
        ]);        
    }
}


