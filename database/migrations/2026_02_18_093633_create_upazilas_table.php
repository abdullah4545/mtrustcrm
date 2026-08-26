<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('upazilas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['division_id','district_id','name','code','is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upazilas');
    }
};
