<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations','phone_numbers')) $table->json('phone_numbers')->nullable()->after('phone_secondary');
            if (!Schema::hasColumn('organizations','email_addresses')) $table->json('email_addresses')->nullable()->after('email');
        });
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('organization_id')->nullable()->change();
            $table->string('organization_name')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('activities', function (Blueprint $table) {
            $table->integer('organization_id')->nullable(false)->change();
            $table->string('organization_name')->nullable(false)->change();
        });
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations','email_addresses')) $table->dropColumn('email_addresses');
            if (Schema::hasColumn('organizations','phone_numbers')) $table->dropColumn('phone_numbers');
        });
    }
};
