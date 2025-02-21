<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IndustryType;
use Carbon\Carbon;

class IndustryTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IndustryType::truncate();

        $industryTypes = [
            ['title' => 'Accounting'],
            ['title' => 'Advertising'],
            ['title' => 'Aerospace'],
            ['title' => 'Agriculture'],
            ['title' => 'Aircraft'],
            ['title' => 'Airline'],
            ['title' => 'Apparel & Accessories'],
            ['title' => 'Automotive'],
            ['title' => 'Banking'],
            ['title' => 'Biotechnology'],
            ['title' => 'Broadcasting'],
            ['title' => 'Brokerage'],
            ['title' => 'Call Centers'],
            ['title' => 'Cargo Handling'],
            ['title' => 'Chemicals'],
            ['title' => 'Computer'],
            ['title' => 'Construction'],
            ['title' => 'Consulting'],
            ['title' => 'Consumer Products'],
            ['title' => 'Cosmetics'],
            ['title' => 'Defense'],
            ['title' => 'Department Stores'],
            ['title' => 'Ecommerce'],
            ['title' => 'Education'],
            ['title' => 'Electronics'],
            ['title' => 'Energy'],
            ['title' => 'Engineering'],
            ['title' => 'Entertainment & Leisure'],
            ['title' => 'Executive Search'],
            ['title' => 'Financial Services'],
            ['title' => 'Food, Beverage & Tobacco'],
            ['title' => 'Gaming'],
            ['title' => 'Government'],
            ['title' => 'Grocery'],
            ['title' => 'Health Care'],
            ['title' => 'Hospitality'],
            ['title' => 'Insurance'],
            ['title' => 'Internet Publishing'],
            ['title' => 'Investment Banking'],
            ['title' => 'Legal'],
            ['title' => 'Machinery'],
            ['title' => 'Manufacturing'],
            ['title' => 'Media'],
            ['title' => 'Motion Picture & Video'],
            ['title' => 'Music'],
            ['title' => 'Newspaper Publishers'],
            ['title' => 'Not for Profit'],
            ['title' => 'Online Auctions'],
            ['title' => 'Other'],
            ['title' => 'Pension Funds'],
            ['title' => 'Pharmaceuticals'],
            ['title' => 'Private Equity'],
            ['title' => 'Publishing'],
            ['title' => 'Real Estate'],
            ['title' => 'Retail & Wholesale'],
            ['title' => 'Securities & Commodity Exchanges'],
            ['title' => 'Service'],
            ['title' => 'Shipping'],
            ['title' => 'Soap & Detergent'],
            ['title' => 'Software'],
            ['title' => 'Sports'],
            ['title' => 'Technology'],
            ['title' => 'Telecommunications'],
            ['title' => 'Television'],
            ['title' => 'Transportation'],
            ['title' => 'Trucking'],
            ['title' => 'Utilities'],
            ['title' => 'Venture Capital'],
        ];

        foreach ($industryTypes as $industry) {
            IndustryType::create([
                'title' => $industry['title'],
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
