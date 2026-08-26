<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_area_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            // NULL means: all upazilas under this district.
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['user_id','district_id','upazila_id'], 'uaa_user_geo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_area_assignments');
    }
};
