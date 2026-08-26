<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('quotation_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();

      // product optional (custom item allowed)
      $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

      $table->string('item_name', 200); // product name snapshot OR custom name
      $table->text('description')->nullable();

      $table->decimal('qty', 12, 2)->default(1);
      $table->string('unit', 20)->default('pcs');

      $table->decimal('unit_price', 12, 2)->default(0);
      $table->decimal('tax_rate', 8, 2)->default(0); // %
      $table->decimal('tax_amount', 12, 2)->default(0);

      $table->decimal('line_total', 12, 2)->default(0); // qty*unit_price + tax_amount
      $table->timestamps();
    });
  }

  public function down(): void {
    Schema::dropIfExists('quotation_items');
  }
};