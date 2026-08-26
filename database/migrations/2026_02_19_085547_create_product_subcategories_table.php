<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();

            $table->string('name');
            $table->string('image')->nullable();
            $table->string('status')->default('active'); // active/inactive
            $table->timestamps();

            // ✅ keep index names short to avoid mysql 1059 error
            $table->index(['category_id','status'], 'psc_cat_status_idx');
            $table->unique(['category_id','name'], 'psc_cat_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_subcategories');
    }
};
