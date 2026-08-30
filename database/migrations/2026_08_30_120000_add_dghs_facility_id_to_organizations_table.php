<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'dghs_facility_id')) {
                $table->string('dghs_facility_id', 50)->nullable()->unique()->after('map_location_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'dghs_facility_id')) {
                $table->dropUnique(['dghs_facility_id']);
                $table->dropColumn('dghs_facility_id');
            }
        });
    }
};
