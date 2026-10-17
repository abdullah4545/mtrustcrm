<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('organization_contacts', 'first_name')) {
            Schema::table('organization_contacts', fn(Blueprint $table) => $table->string('first_name')->nullable());
        }
        if (!Schema::hasColumn('organization_contacts', 'last_name')) {
            Schema::table('organization_contacts', fn(Blueprint $table) => $table->string('last_name')->nullable());
        }
        // Preserve the complete original name verbatim; do not infer surname boundaries.
        // Keep legacy title/name columns for old records and integrations.
        DB::table('organization_contacts')->whereNull('first_name')->whereNull('last_name')
            ->whereNotNull('name')->update(['first_name'=>DB::raw('name')]);
    }

    public function down(): void
    {
        // Deliberately non-destructive: retain both new fields and legacy data.
    }
};
