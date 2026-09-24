<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('activity_travels', function (Blueprint $table) { $table->dateTime('entry_at')->nullable()->after('activity_id'); });
        Schema::table('activity_expenses', function (Blueprint $table) { $table->dateTime('entry_at')->nullable()->after('activity_id'); });
    }
    public function down(): void {
        Schema::table('activity_travels', function (Blueprint $table) { $table->dropColumn('entry_at'); });
        Schema::table('activity_expenses', function (Blueprint $table) { $table->dropColumn('entry_at'); });
    }
};
