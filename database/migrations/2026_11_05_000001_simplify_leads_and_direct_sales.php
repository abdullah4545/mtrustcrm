<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'opening_paid_total')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('opening_paid_total', 12, 2)->default(0);
            });
        }
        foreach (['leads', 'lead_activities'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('next_action_type', 255)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // Keep financial opening balances and free-text actions intact on rollback.
    }
};
