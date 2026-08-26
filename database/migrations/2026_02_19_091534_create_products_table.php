<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('product_subcategories')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->cascadeOnUpdate()->nullOnDelete();

            $table->string('sku')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);

            $table->decimal('vat_rate', 6, 2)->default(0); // %
            $table->decimal('tax_rate', 6, 2)->default(0); // %

            $table->integer('warranty_months')->nullable();
            $table->text('warranty_terms_details')->nullable();

            $table->text('configuration_description')->nullable();

            $table->string('image_url')->nullable();

            $table->string('status')->default('active'); // active/inactive
            $table->softDeletes();
            $table->timestamps();

            // ✅ shorter index name (avoid mysql long index name error)
            $table->index(['category_id','subcategory_id','brand_id','status'], 'prd_filter_idx');
            $table->index(['name'], 'prd_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
