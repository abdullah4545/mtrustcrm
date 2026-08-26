<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->index('date', 'activities_date_index');
            $table->index('created_by', 'activities_created_by_index');
            $table->index('branch_id', 'activities_branch_id_index');
            $table->index('status', 'activities_status_index');
            $table->index(
                'organization_id',
                'activities_organization_id_index'
            );

            $table->index(
                ['created_by', 'date'],
                'activities_created_by_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('activities_date_index');
            $table->dropIndex('activities_created_by_index');
            $table->dropIndex('activities_branch_id_index');
            $table->dropIndex('activities_status_index');
            $table->dropIndex('activities_organization_id_index');
            $table->dropIndex('activities_created_by_date_index');
        });
    }
};