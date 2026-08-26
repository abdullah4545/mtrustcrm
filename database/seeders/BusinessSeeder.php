<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Business;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        Business::updateOrCreate(
            ['id' => 1],
            [
                'business_name' => 'MEDI TRUST SOLUTION',
                'business_email' => 'meditrustsolution@gmail.com',
                'business_phone' => '+88 01711-924911, 01711-220161, 01711-994343',
                'business_address' => 'House # 1148, Avenue # 10, Road # 9/A, Mirpur DOHS, Mirpur, Dhaka-1216, Bangladesh',
                'timezone' => 'Asia/Dhaka',
                'currency' => 'BDT',
                'currency_symbol' => '৳',
                'vat' => 0,
                'logo' => 'public/branding/mts-logo.png',
                'fav_icon' => 'public/branding/mts-logo.png',
                'title' => 'Medi Trust Solution CRM',
                'meta_title' => 'Medi Trust Solution',
                'meta_description' => 'Enterprise CRM workspace for Medi Trust Solution',
            ]
        );
    }
}
