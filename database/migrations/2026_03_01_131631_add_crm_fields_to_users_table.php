<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // ✅ Branch + Created By
            $table->foreignId('branch_id')->nullable()->after('id')
                ->constrained('branches')->nullOnDelete();

            $table->foreignId('added_by')->nullable()->after('branch_id')
                ->constrained('users')->nullOnDelete();

            // ✅ Profile fields
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('present_address')->nullable()->after('phone');
            $table->string('parmanent_address')->nullable()->after('present_address');
            $table->string('profile')->nullable()->after('parmanent_address'); // image path

            $table->date('join_date')->nullable()->after('profile');
            $table->timestamp('last_login_at')->nullable()->after('join_date');

            $table->boolean('status')->default(true)->after('last_login_at');

            // ✅ Geo for filtering (optional but recommended)
            $table->foreignId('division_id')->nullable()->after('status')->constrained('divisions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('division_id')->constrained('districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->after('district_id')->constrained('upazilas')->nullOnDelete();
            $table->foreignId('union_id')->nullable()->after('upazila_id')->constrained('unions')->nullOnDelete();

            $table->index(['branch_id','status']);
            $table->index(['phone']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('added_by');
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('district_id');
            $table->dropConstrainedForeignId('upazila_id');
            $table->dropConstrainedForeignId('union_id');

            $table->dropColumn([
                'phone','present_address','parmanent_address','profile',
                'join_date','last_login_at','status',
            ]);
        });
    }
};