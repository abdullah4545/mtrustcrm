<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities','department_id')) $table->unsignedBigInteger('department_id')->nullable()->after('organization_name');
            if (!Schema::hasColumn('activities','contact_id')) $table->unsignedBigInteger('contact_id')->nullable()->after('department');
            if (!Schema::hasColumn('activities','distance')) $table->decimal('distance',10,2)->nullable()->after('to_location');
        });
    }
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            foreach (['department_id','contact_id','distance'] as $column) if (Schema::hasColumn('activities',$column)) $table->dropColumn($column);
        });
    }
};
