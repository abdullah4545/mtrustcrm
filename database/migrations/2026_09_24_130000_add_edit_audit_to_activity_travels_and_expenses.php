<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['activity_travels', 'activity_expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedInteger('edit_count')->default(0);
                $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('last_edited_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['activity_travels', 'activity_expenses'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('last_edited_by');
                $table->dropColumn(['edit_count', 'last_edited_at']);
            });
        }
    }
};
