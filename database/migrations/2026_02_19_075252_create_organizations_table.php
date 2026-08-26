<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_category_id')->nullable()
                ->constrained('organization_categories')->nullOnDelete();

            $table->foreignId('organization_type_id')->nullable()
                ->constrained('organization_types')->nullOnDelete();

            $table->string('name', 200);
            $table->string('address', 255)->nullable();

            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete();
            $table->foreignId('union_id')->nullable()->constrained('unions')->nullOnDelete();

            $table->string('phone_primary', 30)->nullable();
            $table->string('phone_secondary', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('website', 150)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->text('notes')->nullable();
            $table->longText('about_us')->nullable();

            // status: active/inactive
            $table->string('status', 20)->default('active');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // ✅ short index names (avoid MySQL 1059)
            $table->index(['organization_category_id'], 'org_cat_idx');
            $table->index(['organization_type_id'], 'org_type_idx');
            $table->index(['division_id','district_id','upazila_id','union_id'], 'org_geo_idx');
            $table->index(['status'], 'org_status_idx');
            $table->index(['name'], 'org_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
