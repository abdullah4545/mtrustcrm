<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->string('branch_code', 30)->unique();
            $table->string('branch_name', 150);

            $table->foreignId('parent_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->boolean('is_main_branch')->default(false);

            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 190)->nullable();

            $table->boolean('is_active')->default(true);

            // ✅ geo (one branch = one geo)
            $table->foreignId('division_id')->constrained('divisions')->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();

            $table->index(['division_id','district_id','upazila_id','union_id'], 'branches_geo_idx');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};