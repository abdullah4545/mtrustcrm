<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('businesses')->where('id', 1)->exists();
        $data = [
            'business_name' => 'MEDI TRUST SOLUTION',
            'business_email' => 'meditrustsolution@gmail.com',
            'business_phone' => '+88 01711-924911, 01711-220161, 01711-994343',
            'business_address' => 'House # 1148, Avenue # 10, Road # 9/A, Mirpur DOHS, Mirpur, Dhaka-1216, Bangladesh',
            'timezone' => 'Asia/Dhaka',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'logo' => 'public/branding/mts-logo.png',
            'fav_icon' => 'public/branding/mts-logo.png',
            'title' => 'Medi Trust Solution CRM',
            'meta_title' => 'Medi Trust Solution',
            'meta_description' => 'Enterprise CRM workspace for Medi Trust Solution',
            'updated_at' => now(),
        ];
        if ($exists) DB::table('businesses')->where('id', 1)->update($data);
        else DB::table('businesses')->insert(array_merge(['id'=>1,'vat'=>0,'created_at'=>now()], $data));
    }

    public function down(): void
    {
        // Branding data is intentionally kept on rollback to avoid replacing uploaded company identity.
    }
};
