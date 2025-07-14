<?php

namespace Database\Seeders;

use App\Models\EvaluationQuestionUsageType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluationQuestionUsageTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usageTypes = [
            ['usage_type' => 'Calls', 'status' => 'active'],
            ['usage_type' => 'Support Ticket', 'status' => 'active'],
        ];

        foreach ($usageTypes as $usageType) {
            EvaluationQuestionUsageType::updateOrCreate(['usage_type' => $usageType['usage_type']], $usageType);
        }
    }
}
