<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->foreignId('upazila_id')->constrained('upazilas')->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // ✅ short index names (fix MySQL 1059)
            $table->index(['division_id','district_id','upazila_id'], 'unions_geo_idx');
            $table->index(['name'], 'unions_name_idx');
            $table->index(['code'], 'unions_code_idx');
            $table->index(['is_active'], 'unions_active_idx');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('unions');
    }
};
