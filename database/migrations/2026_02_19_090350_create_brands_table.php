<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('image')->nullable();
            $table->string('status')->default('active'); // active/inactive
            $table->timestamps();

            $table->index(['status'], 'brands_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
