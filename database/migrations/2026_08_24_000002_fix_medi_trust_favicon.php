<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $business = DB::table('businesses')->where('id', 1)->first();

        if (!$business) {
            return;
        }

        $current = trim((string) ($business->fav_icon ?? ''));
        $mediTrust = strtoupper(trim((string) ($business->business_name ?? ''))) === 'MEDI TRUST SOLUTION';

        if ($mediTrust && ($current === '' || $current === 'public/branding/mts-logo.png' || $current === 'branding/mts-logo.png')) {
            DB::table('businesses')->where('id', 1)->update([
                'fav_icon' => 'public/branding/mts-favicon.png',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep the selected company favicon on rollback.
    }
};
